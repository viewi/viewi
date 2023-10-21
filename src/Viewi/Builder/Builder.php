<?php

namespace Viewi\Builder;

use Exception;
use Reflection;
use ReflectionClass;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionProperty;
use Viewi\Builder\Attributes\CustomJs;
use Viewi\Builder\Attributes\Skip;
use Viewi\Components\BaseComponent;
use Viewi\Components\DOM\HtmlNode;
use Viewi\DI\Scoped;
use Viewi\DI\Singleton;
use Viewi\ViewiPath;
use Viewi\Helpers;
use Viewi\JsTranspile\BaseFunction;
use Viewi\JsTranspile\ExportItem;
use Viewi\JsTranspile\JsOutput;
use Viewi\JsTranspile\JsTranspiler;
use Viewi\JsTranspile\UseItem;
use Viewi\Router\ComponentRoute;
use Viewi\Router\Router;
use Viewi\TemplateCompiler\TemplateCompiler;
use Viewi\TemplateParser\TagItemConverter;
use Viewi\TemplateParser\TemplateParser;

class Builder
{
    private TemplateParser $templateParser;
    private JsTranspiler $jsTranspiler;
    private TemplateCompiler $templateCompiler;
    /**
     * 
     * @var array<string, BuildItem>
     */
    private array $components;

    private array $avaliableComponents;
    /**
     * 
     * @var array<string, BaseFunction>
     */
    private array $avaliableFunctions;
    /**
     * 
     * @var array<string, BaseFunction>
     */
    private array $usedFunctions;
    // Config placeholders
    private bool $shakeTree = true;
    private string $buildPath = '';
    private string $jsPath = '';
    private string $publicPath = '';
    // Keep it as associative array
    /**
     * 
     * @var array{meta: array, components: array}
     */
    private array $meta = [];
    private array $systemClasses = [
        Attribute::class => true,
        Exception::class => true,
        Singleton::class => true,
        Scoped::class => true,
        Skip::class => true,
        CustomJs::class => true
    ];

    private array $hookMethods = [
        'init' => true,
        'mount' => true,
        'mounted' => true,
        'rendered' => true,
        'destroy' => true,
    ];

    public function __construct(private Router $router)
    {
        $this->templateParser = new TemplateParser();
        $this->jsTranspiler = new JsTranspiler();
        $this->templateCompiler = new TemplateCompiler($this->jsTranspiler);
    }

    // collect files,
    // parse template,
    // transpile to js,
    // build php render script,
    // cache metadata (optional)
    // return metadata

    public function build(string $entryPath, array $includes, string $buildPath, string $jsPath, string $publicPath)
    {
        $this->reset();
        $this->buildPath = $buildPath;
        $this->jsPath = $jsPath;
        $this->publicPath = $publicPath;
        $d = DIRECTORY_SEPARATOR;
        // $includes will be shaken if not used in the $entryPath
        // 1. collect avaliable components
        // 2. transpile to js and collect uses, props, methods and paths
        $this->avaliableComponents = [];
        $this->usedFunctions = [];
        $this->avaliableFunctions = require ViewiPath::dir() . $d . 'JsTranspile' . $d . 'functions.php';
        $this->collectComponents($entryPath, true);
        foreach ([...$includes, $this->getCoreComponentsPath()] as $path) {
            $this->collectComponents($path, !$this->shakeTree);
        }
        // Helpers::debug($this->components);
        // 3. validate components and parse html templates
        // 4. validate and build template:
        //      render function, 
        //      expressions,
        //      mark used components,
        //      collect reactivity deps
        $this->templateParser->setAvaliableComponents(array_flip(array_keys($this->components)));
        foreach ($this->components as $buildItem) {
            $class = $buildItem->Namespace . '\\' . $buildItem->ComponentName;
            $buildItem->ReflectionClass = new ReflectionClass($class);
            $buildItem->Props = $this->getProps($buildItem->ReflectionClass);
            $buildItem->Methods = $this->getMethods($buildItem->ReflectionClass);
            $this->validateAndParseTemplate($buildItem);
        }
        // 5. cache metadata on each step if enabled
        // 6. return metadata
        // Helpers::debug(array_flip(array_keys($this->components)));
        // Helpers::debug($this->avaliableComponents);
        // Helpers::debug($this->usedFunctions);        
        $this->collectHtmlRootComponentName();
        // create files
        $this->makeFiles();
        // Helpers::debug($this->meta);
        // Helpers::debug($this->components);
    }

    private function reset()
    {
        $this->components = [];
        $this->meta = ['components' => [], 'map' => [], 'buildPath' => ''];
    }

    private function getCoreComponentsPath(): string
    {
        return ViewiPath::dir() . DIRECTORY_SEPARATOR . 'Components';
    }

    /**
     * 
     * @param JsOutput $jsOutput 
     * @param array<string, ExportItem> $exports
     * @param bool $include
     * @return void 
     */
    private function collectExports(JsOutput $jsOutput, array $exports, bool $include = false)
    {
        foreach ($exports as $exportItem) {
            if ($exportItem->Type === ExportItem::Namespace) {
                $this->collectExports($jsOutput, $exportItem->Children, $include);
            } elseif ($exportItem->Type === ExportItem::Class_) {
                $this->components[$exportItem->Name] = new BuildItem($exportItem->Name, $jsOutput, $include);
                $this->components[$exportItem->Name]->Uses = $jsOutput->getUses();
                if ($exportItem->Attributes !== null) {
                    if (isset($exportItem->Attributes['extends'])) {
                        $this->components[$exportItem->Name]->Extends = $exportItem->Attributes['extends'];
                    }
                    if (isset($exportItem->Attributes['namespace'])) {
                        $this->components[$exportItem->Name]->Namespace = $exportItem->Attributes['namespace'];
                    }
                    if (isset($exportItem->Attributes['attrs'])) {
                        $this->components[$exportItem->Name]->Attributes = $exportItem->Attributes['attrs'];
                        if ($this->components[$exportItem->Name]->Attributes) {
                            if (isset($this->components[$exportItem->Name]->Attributes['Skip'])) {
                                $this->components[$exportItem->Name]->Skip = true;
                            }
                            if (isset($this->components[$exportItem->Name]->Attributes['CustomJs'])) {
                                $this->components[$exportItem->Name]->CustomJs = true;
                            }
                        }
                    }
                }
                if (!$this->components[$exportItem->Name]->CustomJs && !$this->components[$exportItem->Name]->Skip) {
                    $this->collectPublicNodes($this->components[$exportItem->Name], $exportItem->Children);
                }
            }
        }
    }

    /**
     * 
     * @param BuildItem $buildItem 
     * @param array<string, ExportItem> $exports 
     * @return void 
     */
    private function collectPublicNodes(BuildItem $buildItem, array $exports)
    {
        foreach ($exports as $exportItem) {
            if ($exportItem->Type === ExportItem::Property || $exportItem->Type === ExportItem::Method) {
                $buildItem->publicNodes[$exportItem->Name] = $exportItem->Type;
                if ($exportItem->DataType === 'HtmlNode') {
                    $buildItem->refs[$exportItem->Name] = 1;
                }
            }
        }
    }

    private function collectComponents(string $path, bool $include = false)
    {
        $files = Helpers::collectFiles($path);
        foreach ($files as $filePath => $_) {
            $pathinfo = pathinfo($filePath);
            $extension = $pathinfo['extension'] ?? null;
            if ($extension === 'php') {
                $jsOutput = $this->jsTranspiler->convert(file_get_contents($filePath));
                $this->collectExports($jsOutput, $jsOutput->getExports(), $include);
                $templatePath = $pathinfo['dirname'] . DIRECTORY_SEPARATOR . $pathinfo['filename'] . '.html';
                if (is_file($templatePath) && isset($this->components[$pathinfo['filename']])) {
                    $this->components[$pathinfo['filename']]->TemplatePath = $templatePath;
                    $this->avaliableComponents[$pathinfo['filename']] = true;
                }
            }
        }
    }

    private function collectIncludes(BuildItem $buildItem)
    {
        foreach ($buildItem->Uses as $baseName => $useItem) {
            if ($useItem->Type === UseItem::Class_) {
                if (!isset($this->components[$baseName])) {
                    $className = implode('\\', $useItem->Parts);
                    if (!isset($this->systemClasses[$className])) {
                        // Helpers::debug([$this->systemClasses]);
                        throw new Exception("Class '$className' can not be found.");
                    }
                    $useItem->Skip = true;
                    continue;
                }
                if ($this->components[$baseName]->Skip) {
                    continue;
                }
                if (!$this->components[$baseName]->Include) {
                    $this->components[$baseName]->Include = true;
                    // Helpers::debug([$baseName]);
                    if (!($this->components[$baseName]->CustomJs || $this->components[$baseName]->Skip)) {
                        $this->collectIncludes($this->components[$baseName]);
                    }
                }
            } elseif ($useItem->Type === UseItem::Function) {
                $this->usedFunctions[$baseName] = $this->avaliableFunctions[$baseName];
            }
        }
    }

    private function collectExtends(BuildItem $buildItem, BuildItem $extendBuildItem)
    {
        if ($extendBuildItem->Extends != null) {
            foreach ($extendBuildItem->Extends as $extendClass) {
                if (!isset($this->components[$extendClass])) {
                    throw new Exception("Class '$extendClass' can not be found."); // TODO: create exception classes                    
                }
                $buildItem->publicNodes = array_merge($this->components[$extendClass]->publicNodes, $buildItem->publicNodes);
                $this->collectExtends($buildItem, $this->components[$extendClass]);
            }
        }
    }

    private function validateAndParseTemplate(BuildItem $buildItem)
    {
        if (!$buildItem->Ready) {
            $buildItem->Ready = true;
            if (!($buildItem->CustomJs || $buildItem->Skip)) {
                // 1. validate uses
                // 2. validate core functions
                foreach ($buildItem->Uses as $baseName => $useItem) {
                    if ($useItem->Type === UseItem::Class_) {
                        if (!isset($this->components[$baseName])) {
                            $fullName = implode('\\', $useItem->Parts);
                            if (!isset($this->systemClasses[$fullName])) {
                                throw new Exception("Class '$fullName' can not be found or is used outside of your source paths."); // TODO: create exception classes
                            }
                            $useItem->Skip = true;
                        }
                    } elseif ($useItem->Type === UseItem::Function) {
                        if (!isset($this->avaliableFunctions[$baseName])) {
                            $fullName = implode('\\', $useItem->Parts);
                            throw new Exception("Function '$fullName' can not be found or is used outside of your source paths."); // TODO: create exception classes
                        }
                    }
                }
                foreach ($buildItem->Props as $prop => $_) {
                    $buildItem->publicNodes[$prop] = ExportItem::Property;
                }
                foreach ($buildItem->Methods as $method => $_) {
                    $buildItem->publicNodes[$method] = ExportItem::Method;
                }
                $this->collectExtends($buildItem, $buildItem);
                // 3. parse and compile template if exists
                // 4. transpile and validate expressions
                if ($buildItem->TemplatePath !== null) {
                    $rootTag = $this->templateParser->parse(file_get_contents($buildItem->TemplatePath));
                    $template = $this->templateCompiler->compile($rootTag, $buildItem);
                    foreach ($template->usedFunctions as $funcName => $_) {
                        if (!isset($this->avaliableFunctions[$funcName])) {
                            throw new Exception("Function '$funcName' can not be found or is used outside of your source paths."); // TODO: create exception classes
                        }
                        if (!isset($buildItem->Uses[$funcName])) {
                            $buildItem->Uses[$funcName] = new UseItem([$funcName], UseItem::Function);
                        }
                    }
                    $buildItem->RenderFunction = $template;
                    $buildItem->RootTag = $rootTag;
                    // Helpers::debug([$buildItem->ComponentName, $template->usedComponents, $template->hasHtmlTag]);
                }

                if ($buildItem->Include) {
                    // Helpers::debug([$buildItem->ComponentName, $buildItem->CustomJs, $buildItem->Skip]);
                    $this->collectIncludes($buildItem);
                }
            }
        }
    }

    private function getHtmlRootComponent(BuildItem $buildItem): ?string
    {
        if (!$buildItem->HtmlRootComponentCalculated) {
            $buildItem->HtmlRootComponentCalculated = true;
            if ($buildItem->RenderFunction->hasHtmlTag) {
                $buildItem->HtmlRootComponent = $buildItem->ComponentName;
                return $buildItem->ComponentName;
            }
            foreach ($buildItem->RenderFunction->usedComponents as $name => $_) {
                $htmlComponent = $this->getHtmlRootComponent($this->components[$name]);
                if ($htmlComponent !== null) {
                    $buildItem->HtmlRootComponent = $htmlComponent;
                    break;
                }
            }
        }
        return $buildItem->HtmlRootComponent;
    }

    private function collectHtmlRootComponentName()
    {
        foreach ($this->components as $buildItem) {
            if ($buildItem->RenderFunction !== null) {
                $this->getHtmlRootComponent($buildItem);
            }
            // Helpers::debug([$buildItem->ComponentName, $buildItem->HtmlRootComponent]);
        }
    }

    private function makeFiles()
    {
        $d = DIRECTORY_SEPARATOR;
        if (!file_exists($this->buildPath)) {
            mkdir($this->buildPath, 0777, true);
        }
        Helpers::removeDirectory($this->buildPath);
        $jsPath = $this->jsPath . $d . 'app' . $d . 'components'; // TODO: clean up before
        $jsFunctionsPath = $this->jsPath . $d . 'app' . $d . 'functions'; // TODO: clean up before
        if (!file_exists($jsPath)) {
            mkdir($jsPath, 0777, true);
        }
        Helpers::removeDirectory($jsPath);
        if (!file_exists($jsFunctionsPath)) {
            mkdir($jsFunctionsPath, 0777, true);
        }
        Helpers::removeDirectory($jsFunctionsPath);
        $componentsIndexJs = '';
        $componentsExportList = '';
        $publicJson = [];
        // $this->meta['buildPath'] = $this->buildPath;
        foreach ($this->components as $buildItem) {
            if ($buildItem->Skip || !$buildItem->Include) {
                continue;
            }
            $componentMeta = [
                'Namespace' => $buildItem->Namespace,
                'Name' => $buildItem->ComponentName
            ];
            $publicJson[$buildItem->ComponentName] = [];
            // dependencies, props

            $componentMeta['dependencies'] = $this->getDependencies($buildItem->ReflectionClass);
            $lifecycleHooks = [];
            foreach ($buildItem->Methods as $method => $_) {
                if (isset($this->hookMethods[$method])) {
                    $lifecycleHooks[$method] = 1;
                }
            }
            if ($lifecycleHooks) {
                $componentMeta['hooks'] = $lifecycleHooks;
                $publicJson[$buildItem->ComponentName]['hooks'] = $lifecycleHooks;
            }
            if (!$buildItem->CustomJs && count($componentMeta['dependencies']) > 0) {
                $publicJson[$buildItem->ComponentName]['dependencies'] = [];
                foreach ($componentMeta['dependencies'] as $argumentName => $argumentInfo) {
                    $publicJson[$buildItem->ComponentName]['dependencies'][] = array_merge(['argName' => $argumentName], $argumentInfo);
                }
            }
            $attributes = $buildItem->ReflectionClass->getAttributes();
            foreach ($attributes as $attribute) {
                $attributeClass = $attribute->getName();
                switch ($attributeClass) {
                    case Singleton::class: {
                            $componentMeta['di'] = Singleton::NAME;
                            $publicJson[$buildItem->ComponentName]['di'] = Singleton::NAME;
                            break;
                        }
                    case Scoped::class: {
                            $componentMeta['di'] = Scoped::NAME;
                            $publicJson[$buildItem->ComponentName]['di'] = Scoped::NAME;
                            break;
                        }
                    default: // none 
                        break;
                }
                // Helpers::debug($attributeClass);
            }
            $componentMeta['inputs'] = $buildItem->Props;
            if ($buildItem->ReflectionClass->isSubclassOf(BaseComponent::class)) {
                $componentMeta['base'] = 1;
                $publicJson[$buildItem->ComponentName]['base'] = 1;
                if ($buildItem->refs) {
                    $publicJson[$buildItem->ComponentName]['refs'] = $buildItem->refs;
                }
            }
            if ($buildItem->HtmlRootComponent !== null) {
                $publicJson[$buildItem->ComponentName]['parent'] = $buildItem->HtmlRootComponent;
            }
            // template, render function
            $expressionsJs = '';
            if ($buildItem->RenderFunction !== null) {
                $renderRelativePath = $d .
                    str_replace(array('/', '\\'), $d, ($buildItem->Namespace ?? ''));
                $renderFunctionDir = $this->buildPath . $renderRelativePath;
                $renderFunctionPath = $renderRelativePath . $d .
                    $buildItem->ComponentName . '.php';
                $componentMeta['Path'] = $renderFunctionPath;
                $componentMeta['Function'] = $buildItem->RenderFunction->renderName;
                if (!file_exists($renderFunctionDir)) {
                    mkdir($renderFunctionDir, 0777, true);
                }
                $content = $buildItem->RenderFunction->generatePhpContent();
                file_put_contents($this->buildPath . $renderFunctionPath, $content);
                $this->meta['map'][$buildItem->RenderFunction->renderName] = $buildItem->ComponentName;
                foreach ($buildItem->RenderFunction->slots as $slotTuple) {
                    $this->meta['map'][$slotTuple[1]->renderName] = $buildItem->ComponentName;
                }
                $publicJson[$buildItem->ComponentName]['nodes'] = TagItemConverter::getRaw($buildItem->RootTag);
                // inline expressions
                $exprComma = '';
                foreach ($buildItem->RenderFunction->inlineExpressions as $code => [$expression, $arguments]) {
                    $funcArguments = implode(', ', ['_component', ...$arguments]);
                    $expressionsJs .= $exprComma . "    function ($funcArguments) { return $expression; }";
                    $exprComma = ',' . PHP_EOL;
                }
            }
            $this->meta['components'][$buildItem->ComponentName] = $componentMeta;
            // javascript
            if (!$buildItem->CustomJs) { // $buildItem->ComponentName !== 'BaseComponent'
                $jsComponentPath = $jsPath . $d . $buildItem->ComponentName . '.js';
                $jsComponentCode = '';
                $comma = '';
                $registerIncluded = false;
                $additionalCode = "";
                foreach ($buildItem->Uses as $importName => $useItem) {
                    if (!$useItem->Skip) {
                        if ($useItem->Type === UseItem::Class_) {
                            if ($importName === 'BaseComponent') {
                                $jsComponentCode .= 'import { BaseComponent } from "../../viewi/core/component/baseComponent";' . PHP_EOL;
                            } elseif (isset($this->components[$importName]) && $this->components[$importName]->CustomJs) {
                                if (!$registerIncluded) {
                                    $jsComponentCode .= 'import { register } from "../../viewi/core/di/register"' . PHP_EOL;
                                    $registerIncluded = true;
                                }
                                $additionalCode = "var $importName = register.$importName;" . PHP_EOL;
                            } elseif (!isset($this->components[$importName]) || !$this->components[$importName]->Skip) {
                                $jsComponentCode .= "import { $importName } from \"./$importName\";" . PHP_EOL;
                            }
                        } elseif ($useItem->Type === UseItem::Function) {
                            $jsComponentCode .= "import { $importName } from \"../functions/$importName\";" . PHP_EOL;
                        }
                        $comma = PHP_EOL;
                    }
                }
                // if ($buildItem->RenderFunction !== null) {
                //     $jsComponentCode .= 'import { makeProxy } from "../../viewi/core/makeProxy";' . PHP_EOL;
                //     $comma = PHP_EOL;
                // }
                if ($additionalCode) {
                    $jsComponentCode .= $comma . $additionalCode;
                }
                $jsComponentCode .= $comma . $buildItem->JsOutput->__toString();
                $expressionsImport = '';
                if ($expressionsJs !== '') {
                    $expressionName = $buildItem->ComponentName . '_x';
                    $expressionsJs = PHP_EOL . $expressionsJs . PHP_EOL;
                    $jsComponentCode .= $comma .
                        "export const $expressionName = [$expressionsJs];" . PHP_EOL;
                    $componentsExportList .= PHP_EOL . "    $expressionName,";
                    $expressionsImport = ", $expressionName";
                }
                $jsComponentCode .= PHP_EOL . 'export { ' . $buildItem->ComponentName . ' }';
                file_put_contents($jsComponentPath, $jsComponentCode);
                $componentsIndexJs .= "import { {$buildItem->ComponentName}$expressionsImport } from \"./{$buildItem->ComponentName}\";" . PHP_EOL;
                $componentsExportList .= PHP_EOL . "    {$buildItem->ComponentName},";
            } else {
                $publicJson[$buildItem->ComponentName]['custom'] = 1;
            }
        }
        // export const components = {
        //     Counter,
        //     CounterReducer,
        //     TodoReducer
        // };

        $componentsIndexJs .= PHP_EOL . "export const components = {{$componentsExportList}";
        $componentsIndexJs .= $componentsExportList ? PHP_EOL . '};' : '};';

        $componentsContent = '<?php' . PHP_EOL . 'return ' . var_export($this->meta, true) . ';';
        file_put_contents($this->buildPath . $d . 'components.php', $componentsContent); // TODO: make const or static helper
        // core PHP functions in JS
        $functionsExportList = '';
        $functionsIndexJs = '';
        foreach ($this->usedFunctions as $functionName => $baseFunction) {
            $functionPath = $jsFunctionsPath . $d . $functionName . '.js';
            $functionContent = $baseFunction::getJs();
            $functionContent .= PHP_EOL . "export { $functionName }";
            file_put_contents($functionPath, $functionContent);
            $functionsIndexJs .= "import { $functionName } from \"./{$functionName}\";" . PHP_EOL;
            $functionsExportList .= PHP_EOL . "    {$functionName},";
        }
        $functionsIndexJs .= PHP_EOL . "export const functions = {{$functionsExportList}";
        $functionsIndexJs .= $functionsExportList ? PHP_EOL . '};' : '};';
        // components/index.js
        // functions/index.js
        file_put_contents($jsPath . $d . 'index.js', $componentsIndexJs);
        file_put_contents($jsFunctionsPath . $d . 'index.js', $functionsIndexJs);
        $publicJson['_meta'] = ['boolean' => $this->templateCompiler->getBooleanAttributesString()];
        $publicJson['_routes'] = [];
        $routes = $this->router->getRoutes();
        foreach ($routes as $route) {
            if ($route->action instanceof ComponentRoute) {
                $item = (array)$route;
                $component = $route->action->component;
                $item['action'] = strpos($component, '\\') !== false ?
                    substr(strrchr($component, "\\"), 1)
                    : $component;
                $publicJson['_routes'][] = $item;
            }
        }
        $publicJsonContent = json_encode($publicJson, 0, 1024 * 32);
        file_put_contents($this->jsPath . $d . 'components.json', $publicJsonContent);
        // Run NPM command
        // TODO: watch mode
        // TODO: no node mode (means no minfication and all the node features)
        $npmFolder = $this->jsPath . $d;
        $currentDir = getcwd();
        chdir($npmFolder);
        $command = "npm --prefix $npmFolder run build 2>&1";
        // $command = "npm run build 2>&1"; // test error
        $lastLine = exec($command, $output, $result_code);
        if ($result_code !== 0) {
            Helpers::debug([$output, $lastLine, $result_code]);
            $text = implode(PHP_EOL, $output ?? []) . PHP_EOL . $lastLine;
            throw new Exception("NPM build failed: code $result_code $text");
        }
        $distJsFile = $this->jsPath . $d . 'dist' . $d . 'viewi.js';
        copy($this->jsPath . $d . 'components.json', $this->jsPath . $d . 'dist' . $d . 'components.json');
        $distJsMinFile = $this->jsPath . $d . 'dist' . $d . 'viewi.min.js';
        file_put_contents("$distJsMinFile.gz", gzencode(file_get_contents($distJsMinFile), 5));
        // TODO: configurable paths
        // TODO: configurable minify
        if (!file_exists($distJsFile)) {
            throw new Exception("Could not find Viewi build file at $distJsFile.");
        }
        chdir($currentDir);
        copy($distJsFile, $this->publicPath . $d . 'app.js');
        copy($this->jsPath . $d . 'components.json', $this->publicPath . $d . 'components.json');
    }


    /**
     * 
     * @param ReflectionClass $reflectionClass 
     * @return array 
     */
    private function getProps(ReflectionClass $reflectionClass): array
    {
        $inputs = [];
        $props = $reflectionClass->getProperties(ReflectionProperty::IS_PUBLIC);
        if (count($props) > 0) {
            foreach ($props as $propertyInfo) {
                // $type = $propertyInfo->getType();
                // if ($type !== null && $type->getName() === HtmlNode::class) {

                // }
                $inputs[$propertyInfo->getName()] = 1;
            }
        }
        return $inputs;
    }

    /**
     * 
     * @param ReflectionClass $reflectionClass 
     * @return array 
     */
    private function getMethods(ReflectionClass $reflectionClass): array
    {
        $list = [];
        $methods = $reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC);
        foreach ($methods as $method) {
            if (!$method->isStatic() && !$method->isAbstract()) {
                $list[$method->name] = 1;
            }
        }
        return $list;
    }

    /**
     *
     * @param ReflectionClass $reflectionClass
     * @return array
     * @throws ReflectionException
     */
    private function getDependencies(ReflectionClass $reflectionClass): array
    {
        $dependencies = [];
        $constructor = $reflectionClass->getConstructor();
        if ($constructor !== null) {
            $constructorArgs = $constructor->getParameters();
            if (!empty($constructorArgs)) {

                foreach ($constructorArgs as $argument) {
                    $argumentName = $argument->name;
                    if ($argument->hasType()) {
                        /** @var ReflectionNamedType $namedType */
                        $namedType = $argument->getType();
                        if ($namedType instanceof ReflectionNamedType) {
                            $argumentClass = $argument->getType() && !$argument->getType()->isBuiltin()
                                ? new ReflectionClass($argument->getType()->getName())
                                : null; // check if class exists
                            $dependencies[$argumentName] =
                                [
                                    'name' => $argumentClass ? $argumentClass->getShortName() : $namedType->getName()
                                ];
                            if ($argument->isOptional()) {
                                $dependencies[$argumentName]['optional'] = 1;
                            }
                            if ($argument->isDefaultValueAvailable() && is_null($argumentClass)) {
                                $dependencies[$argumentName]['default'] =
                                    $argument->getDefaultValue();
                            }
                            if ($namedType->isBuiltin()) {
                                $dependencies[$argumentName]['builtIn'] = 1;
                            }
                            if ($namedType->allowsNull()) {
                                $dependencies[$argumentName]['null'] = 1;
                            }
                        }
                    } else {
                        $dependencies[$argumentName] =
                            [
                                'mixed' => 1
                            ];
                        // throw new Exception("Argument '$argumentName' in class" .
                        //     "{$reflectionClass->name}' can`t be resolved without a type in {$reflectionClass->getFileName()}.");
                    }
                }
            }
        }
        return $dependencies;
    }
}
