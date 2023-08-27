<?php

namespace Viewi\Builder;

use Exception;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionProperty;
use Viewi\ViewiPath;
use Viewi\Helpers;
use Viewi\JsTranspile\BaseFunction;
use Viewi\JsTranspile\ExportItem;
use Viewi\JsTranspile\JsOutput;
use Viewi\JsTranspile\JsTranspiler;
use Viewi\JsTranspile\UseItem;
use Viewi\TemplateCompiler\TemplateCompiler;
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
    // Keep it as associative array
    /**
     * 
     * @var array{meta: array, components: array}
     */
    private array $meta = [];

    public function __construct()
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

    public function build(string $entryPath, array $includes, string $buildPath, string $jsPath)
    {
        $this->reset();
        $this->buildPath = $buildPath;
        $this->jsPath = $jsPath;
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
            $this->validateAndParseTemplate($buildItem);
        }
        // 5. cache metadata on each step if enabled
        // 6. return metadata
        // Helpers::debug(array_flip(array_keys($this->components)));
        // Helpers::debug($this->avaliableComponents);
        // Helpers::debug($this->usedFunctions);
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
                }
                $this->collectPublicNodes($this->components[$exportItem->Name], $exportItem->Children);
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
                if (!$this->components[$baseName]->Include) {
                    $this->components[$baseName]->Include = true;
                    $this->collectIncludes($this->components[$baseName]);
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
            // 1. validate uses
            // 2. validate core functions
            foreach ($buildItem->Uses as $baseName => $useItem) {
                if ($useItem->Type === UseItem::Class_) {
                    if (!isset($this->components[$baseName])) {
                        $fullName = implode('\\', $useItem->Parts);
                        throw new Exception("Class '$fullName' can not be found or is used outside of your source paths."); // TODO: create exception classes
                    }
                } elseif ($useItem->Type === UseItem::Function) {
                    if (!isset($this->avaliableFunctions[$baseName])) {
                        $fullName = implode('\\', $useItem->Parts);
                        throw new Exception("Function '$fullName' can not be found or is used outside of your source paths."); // TODO: create exception classes
                    }
                }
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
            }

            if ($buildItem->Include) {
                $this->collectIncludes($buildItem);
            }
        }
    }

    private function makeFiles()
    {
        $d = DIRECTORY_SEPARATOR;
        if (!file_exists($this->buildPath)) {
            mkdir($this->buildPath, 0777, true);
        }
        Helpers::removeDirectory($this->buildPath);
        $jsPath = $this->jsPath . $d . 'components';
        $jsFunctionsPath = $this->jsPath . $d . 'functions';
        if (!file_exists($jsPath)) {
            mkdir($jsPath, 0777, true);
        }
        if (!file_exists($jsFunctionsPath)) {
            mkdir($jsFunctionsPath, 0777, true);
        }
        // $this->meta['buildPath'] = $this->buildPath;
        foreach ($this->components as $buildItem) {
            $componentMeta = [
                'Namespace' => $buildItem->Namespace,
                'Name' => $buildItem->ComponentName
            ];
            // dependencies, props
            $class = $buildItem->Namespace . '\\' . $buildItem->ComponentName;
            $rf = new ReflectionClass($class);
            $componentMeta['dependencies'] = $this->getDependencies($rf);
            $componentMeta['inputs'] = $this->getProps($rf);
            // template, render function
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
            }
            $this->meta['components'][$buildItem->ComponentName] = $componentMeta;
            // javascript
            $jsComponentPath = $jsPath . $d . $buildItem->ComponentName . '.js';
            $jsComponentCode = '';
            $comma = '';
            foreach ($buildItem->Uses as $importName => $useItem) {
                if ($useItem->Type === UseItem::Class_) {
                    $jsComponentCode .= "import { $importName } from \"./$importName\";" . PHP_EOL;
                } elseif ($useItem->Type === UseItem::Function) {
                    $jsComponentCode .= "import { $importName } from \"../functions/$importName\";" . PHP_EOL;
                }
                $comma = PHP_EOL;
            }
            $jsComponentCode .= $comma . $buildItem->JsOutput->__toString();
            $jsComponentCode .= PHP_EOL . 'export { ' . $buildItem->ComponentName . ' }';
            file_put_contents($jsComponentPath, $jsComponentCode);
            // Helpers::debug($buildItem);
        }
        $componentsContent = '<?php' . PHP_EOL . 'return ' . var_export($this->meta, true) . ';';
        file_put_contents($this->buildPath . $d . 'components.php', $componentsContent); // TODO: make const or static helper

        // core PHP functions in JS
        foreach ($this->usedFunctions as $functionName => $baseFunction) {
            $functionPath = $jsFunctionsPath . $d . $functionName . '.js';
            $functionContent = $baseFunction::getJs();
            $functionContent .= PHP_EOL . "export { $functionName }";
            file_put_contents($functionPath, $functionContent);
        }
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
                $inputs[$propertyInfo->getName()] = true;
            }
        }
        return $inputs;
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
                        throw new Exception("Argument '$argumentName' in class" .
                            "{$reflectionClass->name}' can`t be resolved without a type in {$reflectionClass->getFileName()}.");
                    }
                }
            }
        }
        return $dependencies;
    }
}
