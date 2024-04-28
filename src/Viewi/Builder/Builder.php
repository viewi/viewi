<?php

namespace Viewi\Builder;

use Attribute;
use Exception;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionProperty;
use Viewi\AppConfig;
use Viewi\Builder\Attributes\CustomJs;
use Viewi\Builder\Attributes\GlobalEntry;
use Viewi\Builder\Attributes\Skip;
use Viewi\Builder\BuildAction\IPostBuildAction;
use Viewi\Components\Attributes\LazyLoad;
use Viewi\Components\Attributes\Middleware;
use Viewi\Components\Attributes\PostBuildAction;
use Viewi\Components\Attributes\Preserve;
use Viewi\Components\BaseComponent;
use Viewi\Components\IStartUp\IStartUp;
use Viewi\Components\Render\IRenderable;
use Viewi\DI\Inject;
use Viewi\DI\Scope;
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
    private string $publicRootPath = '';
    private string $publicRootUrl = '';
    private string $assetsPath = '';
    private ?string $assetsSourcePath = null;
    private array $publicConfig;
    private string $appName;
    private bool $minifyJs;
    private bool $internalDevMode;
    private bool $appendVersion;
    private bool $combineJsJson;
    private bool $buildJsSourceCode;
    private string $logs;
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
        CustomJs::class => true,
        Inject::class => true,
        Scope::class => true,
        GlobalEntry::class => true
    ];

    private array $systemFunctions = [
        'ensureType' => true,
    ];

    private array $hookMethods = [
        'init' => true,
        'mounting' => true,
        'mounted' => true,
        'rendered' => true,
        'destroy' => true,
    ];

    private array $renderInvocations = [];
    private array $globalEntries = [];
    private array $lazyLoadNamespaces = [];
    private array $ignoreNamespaces = [];
    private array $noJsNamespaces = [];

    public function __construct(private Router $router)
    {
        $this->templateParser = new TemplateParser();
        $this->jsTranspiler = new JsTranspiler();
        $this->templateCompiler = new TemplateCompiler($this->jsTranspiler);
    }

    public function getLogs(): string
    {
        return $this->logs;
    }

    // collect files,
    // parse template,
    // transpile to js,
    // build php render script,
    // cache metadata (optional)
    // return metadata

    /**
     * @throws ReflectionException
     * @throws Exception
     */
    public function build(AppConfig $config, array $publicConfig)
    {
        $d = DIRECTORY_SEPARATOR;
        $this->reset();
        $this->appName = $config->name;
        $this->buildPath = $config->buildPath;
        $this->jsPath = $config->jsPath;
        $subFolderName = $config->getSubFolderName();
        $this->publicRootPath = $config->publicPath;
        $this->publicRootUrl = $config->publicUrl;
        $this->publicPath = $config->publicPath . ($config->publicUrl ? $config->publicUrl : '') . $d . $subFolderName;
        $this->assetsPath = $config->publicUrl . "/$subFolderName";
        $this->assetsSourcePath = $config->assetsPath;
        $this->minifyJs = $config->minifyJs;
        $this->internalDevMode = $config->internalDevMode;
        $this->combineJsJson = $config->combineJsJson;
        $this->appendVersion = $config->appendVersionPath;
        $this->buildJsSourceCode = $config->buildJSwithNode;
        $this->lazyLoadNamespaces = $config->lazyLoadNamespace;
        $this->ignoreNamespaces = $config->ignoreNamespace;
        $this->noJsNamespaces = $config->noJsNamespace;
        $this->jsTranspiler->setSkipNamespaces(array_merge($this->noJsNamespaces, $this->ignoreNamespaces));
        $this->publicConfig = $publicConfig;
        // $includes will be shaken if not used in the $entryPath
        // 1. collect avaliable components
        // 2. transpile to js and collect uses, props, methods and paths
        $this->avaliableComponents = [];
        $this->usedFunctions = [];
        $this->avaliableFunctions = require ViewiPath::dir() . $d . 'JsTranspile' . $d . 'functions.php';
        $this->logs .= "Collecting components from '{$config->sourcePath}'.." . PHP_EOL;
        $this->collectComponents($config->sourcePath, true);
        foreach ([...$config->includes, $this->getCoreComponentsPath()] as $path) {
            $this->logs .= "Collecting components from '{$path}'.." . PHP_EOL;
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
        $this->templateCompiler->setGlobals($this->globalEntries);
        $this->logs .= "Parsing templates and validating.." . PHP_EOL;
        foreach ($this->components as $buildItem) {
            $this->validateAndParseTemplate($buildItem);
        }
        // 5. cache metadata on each step if enabled
        // 6. return metadata
        // Helpers::debug(array_flip(array_keys($this->components)));
        // Helpers::debug($this->avaliableComponents);
        // Helpers::debug($this->usedFunctions);
        $this->logs .= "Collecting metadata.." . PHP_EOL;
        $this->collectHtmlRootComponentName();
        // create files
        $this->logs .= "Building files.." . PHP_EOL;
        $this->makeFiles();
        // Helpers::debug($this->meta);
        // Helpers::debug($this->components);
    }

    private function reset(): void
    {
        $this->logs = '';
        $this->components = [];
        $this->meta = ['components' => [], 'map' => [], 'buildPath' => '', 'publicConfig' => []];
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
    private function collectExports(JsOutput $jsOutput, array $exports, bool $include = false): void
    {
        foreach ($exports as $exportItem) {
            if ($exportItem->Type === ExportItem::Namespace) {
                $this->collectExports($jsOutput, $exportItem->Children, $include);
            } elseif ($exportItem->Type === ExportItem::Class_) {
                $buildItem = new BuildItem($exportItem->Name, $jsOutput, $include);
                $buildItem->Uses = $jsOutput->getUses();

                $this->components[$exportItem->Name] = $buildItem;
                if ($exportItem->Attributes !== null) {
                    if (isset($exportItem->Attributes['extends'])) {
                        $this->components[$exportItem->Name]->Extends = $exportItem->Attributes['extends'];
                    }
                    if (isset($exportItem->Attributes['namespace'])) {
                        $ns = $exportItem->Attributes['namespace'];
                        $this->components[$exportItem->Name]->Namespace = $ns;
                        foreach ($this->lazyLoadNamespaces as $namespace => $groupName) {
                            if (str_starts_with($ns, $namespace)) {
                                $this->components[$exportItem->Name]->LazyLoad = true;
                                $this->components[$exportItem->Name]->LazyLoadName = $groupName;
                            }
                        }
                        foreach ($this->ignoreNamespaces as $namespace) {
                            if (str_starts_with($ns, $namespace)) {
                                $this->components[$exportItem->Name]->Skip = true;
                            }
                        }
                        foreach ($this->noJsNamespaces as $namespace) {
                            if (str_starts_with($ns, $namespace)) {
                                $this->components[$exportItem->Name]->CustomJs = true;
                            }
                        }
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
                            if (isset($this->components[$exportItem->Name]->Attributes['LazyLoad'])) {
                                $this->components[$exportItem->Name]->LazyLoad = true;
                            }
                        }
                    }
                }
                if (!$this->components[$exportItem->Name]->CustomJs && !$this->components[$exportItem->Name]->Skip) {
                    $this->collectPublicNodes($this->components[$exportItem->Name], $exportItem->Children);
                }

                $class = $buildItem->Namespace . '\\' . $buildItem->ComponentName;
                $buildItem->ReflectionClass = new ReflectionClass($class);
                $buildItem->Props = $this->getProps($buildItem);
                $buildItem->Methods = $this->getMethods($buildItem->ReflectionClass);
                $buildItem->StartUp = $buildItem->ReflectionClass->implementsInterface(IStartUp::class);
            }
        }
    }

    /**
     * 
     * @param BuildItem $buildItem 
     * @param array<string, ExportItem> $exports 
     * @return void 
     */
    private function collectPublicNodes(BuildItem $buildItem, array $exports): void
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

    private function collectComponents(string $path, bool $include = false): void
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

    /**
     * @param BuildItem $buildItem
     * @return void
     * @throws Exception
     */
    private function collectIncludes(BuildItem $buildItem): void
    {
        foreach ($buildItem->Uses as $baseName => $useItem) {
            if ($useItem->Type === UseItem::Class_) {
                if (!isset($this->components[$baseName])) {
                    $className = implode('\\', $useItem->Parts);
                    if (class_exists($className)) {
                        if (!isset($this->systemClasses[$className])) {
                            // Helpers::debug([$this->systemClasses]);
                            throw new Exception("Class '$className' can not be found.");
                        }
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
                if (!isset($this->usedFunctions[$baseName])) {
                    if (!isset($this->avaliableFunctions[$baseName])) {
                        throw new Exception("Function '$baseName' can not be found.");
                    }
                    $this->usedFunctions[$baseName] = $this->avaliableFunctions[$baseName];
                    $this->collectFunctionDependencies($this->usedFunctions[$baseName]);
                }
            }
        }
    }

    /**
     * 
     * @param BaseFunction|string $functionMeta 
     * @return void 
     * @throws Exception 
     */
    private function collectFunctionDependencies($functionMeta): void
    {
        foreach ($functionMeta::getUses() as $functionName) {
            if (!isset($this->usedFunctions[$functionName])) {
                if (!isset($this->avaliableFunctions[$functionName])) {
                    throw new Exception("Function '$functionName' can not be found.");
                }
                $this->usedFunctions[$functionName] = $this->avaliableFunctions[$functionName];
                $this->collectFunctionDependencies($this->usedFunctions[$functionName]);
            }
        }
    }

    /**
     * @param BuildItem $buildItem
     * @param BuildItem $extendBuildItem
     * @return void
     * @throws Exception
     */
    private function collectExtends(BuildItem $buildItem, BuildItem $extendBuildItem): void
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

    /**
     * @param BuildItem $buildItem
     * @return void
     * @throws Exception
     */
    private function validateAndParseTemplate(BuildItem $buildItem): void
    {
        if (!$buildItem->Ready) {
            $buildItem->Ready = true;
            if (!$buildItem->Skip) { // $buildItem->CustomJs ||
                // 1. validate uses
                // 2. validate core functions
                if (!$buildItem->CustomJs) {
                    foreach ($buildItem->Uses as $baseName => $useItem) {
                        if ($useItem->Type === UseItem::Class_) {
                            if (!isset($this->components[$baseName])) {
                                $fullName = implode('\\', $useItem->Parts);
                                if (class_exists($fullName)) {
                                    if (!isset($this->systemClasses[$fullName])) {
                                        throw new Exception("Class '$fullName' can not be found or is used outside of your source paths."); // TODO: create exception classes
                                    }
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
                }
                foreach ($buildItem->Props as $prop => $_) {
                    $buildItem->publicNodes[$prop] = ExportItem::Property;
                }
                foreach ($buildItem->Methods as $method => $_) {
                    $buildItem->publicNodes[$method] = ExportItem::Method;
                }
                if (!$buildItem->CustomJs) {
                    $this->collectExtends($buildItem, $buildItem);
                }

                // 3. parse and compile template if exists
                // 4. transpile and validate expressions
                if ($buildItem->TemplatePath !== null) {
                    $rootTag = $this->templateParser->parse(file_get_contents($buildItem->TemplatePath));
                    $template = $this->templateCompiler->compile($rootTag, $buildItem);
                    $this->renderInvocations = $this->array_merge_recursive($this->renderInvocations, $this->templateCompiler->getRenderInvokations());
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
                    foreach ($template->usedComponents as $component => $_) {
                        $buildItem->Uses[$component] = new UseItem([$component], UseItem::Class_);
                    }
                    // Helpers::debug([$buildItem->ComponentName, $template->usedComponents, $template->hasHtmlTag]);
                }

                if ($buildItem->Include && !$buildItem->CustomJs) {
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
            if ($buildItem->RenderFunction !== null) {
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
        }
        return $buildItem->HtmlRootComponent;
    }

    private function collectHtmlRootComponentName(): void
    {
        foreach ($this->components as $buildItem) {
            $this->getHtmlRootComponent($buildItem);
            // Helpers::debug([$buildItem->ComponentName, $buildItem->HtmlRootComponent]);
        }
    }

    private function makeAppFolders(string $subFolder = 'main'): array
    {
        $d = DIRECTORY_SEPARATOR;
        if ($subFolder) { // TODO: validate folder name
            $subFolder .= $d;
        }
        $jsComponentsPath = $this->jsPath . $d . 'app' . $d . $subFolder . 'components';
        $jsFunctionsPath = $this->jsPath . $d . 'app' . $d . $subFolder . 'functions';
        $jsResourcesPath = $this->jsPath . $d . 'app' . $d . $subFolder . 'resources';

        if (!file_exists($jsComponentsPath)) {
            mkdir($jsComponentsPath, 0777, true);
        }
        Helpers::removeDirectory($jsComponentsPath);

        if (!file_exists($jsFunctionsPath)) {
            mkdir($jsFunctionsPath, 0777, true);
        }
        Helpers::removeDirectory($jsFunctionsPath);

        if (!file_exists($jsResourcesPath)) {
            mkdir($jsResourcesPath, 0777, true);
        }
        Helpers::removeDirectory($jsResourcesPath);
        return [$jsComponentsPath, $jsFunctionsPath, $jsResourcesPath];
    }

    /**
     * @return void
     * @throws ReflectionException
     */
    private function makeFiles(): void
    {
        $d = DIRECTORY_SEPARATOR;
        if (!file_exists($this->buildPath)) {
            mkdir($this->buildPath, 0777, true);
        }
        if (!file_exists($this->publicPath)) {
            mkdir($this->publicPath, 0777, true);
        }
        Helpers::removeDirectory($this->publicPath);
        Helpers::removeDirectory($this->buildPath);
        [$jsComponentsPath, $jsFunctionsPath, $jsResourcesPath] = $this->makeAppFolders();
        $chunks = new Chunks();
        $mainChunk = $chunks->create(Chunk::MAIN, $jsComponentsPath, $jsFunctionsPath, $jsResourcesPath);

        $viewiCorePath = $this->jsPath . $d . 'viewi';
        $viewiLazyLoadGroupsModuleFile = $this->jsPath . $d . 'app' . $d . 'lazyGroups.mjs';
        $viewiLazyLoadGroupsModuleContent = '';
        if (!file_exists($viewiCorePath)) {
            mkdir($viewiCorePath, 0777, true);
        }
        $viewiDistPath = $this->jsPath . $d . 'dist';
        if (!file_exists($viewiDistPath)) {
            mkdir($viewiDistPath, 0777, true);
        }
        Helpers::removeDirectory($viewiDistPath);
        $viewiDistAssetsPath = $viewiDistPath . $d . 'assets';
        if (!file_exists($viewiDistAssetsPath)) {
            mkdir($viewiDistAssetsPath, 0777, true);
        }
        if ($this->internalDevMode) {
            Helpers::copyAll(ViewiPath::viewiJsDir() . $d, $this->jsPath . $d);
        }
        Helpers::copyAll(ViewiPath::viewiJsCoreDir() . $d, $this->jsPath . $d . 'viewi' . $d);
        $publicJson = [];
        $this->meta['buildPath'] = $this->buildPath;
        $startups = [];
        $componentFilter = 0; // 0 - main, 1 - lazy load
        $includedInMain = [];
        $includedInGroups = [];
        /**
         * @var IPostBuildAction[] $postBuild
         */
        $postBuild = [];
        $routes = $this->router->getRoutes();
        $publicRoutes = [];
        foreach ($routes as $route) {
            if ($route->action instanceof ComponentRoute) {
                $item = (array)$route;
                $component = $route->action->component;
                $item['action'] = strpos($component, '\\') !== false ?
                    substr(strrchr($component, "\\"), 1)
                    : $component;
                unset($item['transformCallback']);
                unset($item['lazyGroup']);
                $publicRoutes[] = $item;
                if ($route->action->lazyGroup !== null) {
                    $this->components[$item['action']]->LazyLoad = true;
                    $this->components[$item['action']]->LazyLoadName = $route->action->lazyGroup;
                }
            }
        }
        /** COMPONENTS FOREACH **/
        while ($componentFilter < 2) {
            $componentFilter++;
            foreach ($this->components as $buildItem) {
                if ($buildItem->Skip || !$buildItem->Include) {
                    continue;
                }
                if (
                    ($componentFilter === 1 && $buildItem->LazyLoad)
                    ||
                    ($componentFilter === 2 && !$buildItem->LazyLoad)
                ) {
                    continue;
                }
                $currentChunk = $mainChunk;
                /**
                 * @var string|false
                 */
                $lazyLoadGroup = $buildItem->LazyLoad && $buildItem->LazyLoadName !== null ? $buildItem->LazyLoadName : false;
                $componentMeta = [
                    'Namespace' => $buildItem->Namespace,
                    'Name' => $buildItem->ComponentName
                ];
                $publicJson[$buildItem->ComponentName] = [];
                // dependencies, props

                $componentMeta['dependencies'] = $this->getDependencies($buildItem->ReflectionClass);
                if (count($buildItem->DiProps) > 0) {
                    $componentMeta['diProps'] = $buildItem->DiProps;
                    $publicJson[$buildItem->ComponentName]['diProps'] = $buildItem->DiProps;
                }
                if ($buildItem->ReflectionClass->implementsInterface(IRenderable::class)) {
                    $componentMeta['renderer'] = true;
                    $publicJson[$buildItem->ComponentName]['renderer'] = true;
                }
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
                        case Middleware::class: {
                                /**
                                 * @var Middleware $middlewareAttribute
                                 */
                                $middlewareAttribute = $attribute->newInstance();
                                $shortNames = array_map(function (string $className) {
                                    $exp = explode('\\', $className);
                                    return array_pop($exp);
                                }, $middlewareAttribute->middlewareList);
                                $componentMeta['middleware'] = $shortNames;
                                $publicJson[$buildItem->ComponentName]['middleware'] = $shortNames;
                                break;
                            }
                        case LazyLoad::class: {
                                /**
                                 * @var LazyLoad $lazyAttribute
                                 */
                                $lazyAttribute = $attribute->newInstance();
                                $lazyLoadGroup = !$lazyAttribute->groupName ? $buildItem->ComponentName : $lazyAttribute->groupName;
                                break;
                            }
                        case PostBuildAction::class: {
                                /**
                                 * @var PostBuildAction $postBuildAttributeInstance
                                 */
                                $postBuildAttributeInstance = $attribute->newInstance();
                                $actionClass = $postBuildAttributeInstance->className;
                                $actionInstance = new $actionClass();
                                if ($actionInstance instanceof IPostBuildAction) {
                                    $postBuild[$buildItem->ComponentName] = $actionInstance;
                                }
                                break;
                            }
                        default: // none 
                            break;
                    }
                    // Helpers::debug($attributeClass);
                }
                if ($lazyLoadGroup) {
                    $componentMeta['lazy'] = $lazyLoadGroup;
                    $publicJson[$buildItem->ComponentName]['lazy'] = $lazyLoadGroup;
                    $lazyLoadGroup = $lazyLoadGroup;
                    if (isset($chunks->chunks[$lazyLoadGroup])) {
                        $currentChunk = $chunks->chunks[$lazyLoadGroup];
                    } else {
                        [$jsLazyComponentsPath, $jsLazyFunctionsPath, $jsLazyResourcesPath] = $this->makeAppFolders($lazyLoadGroup);
                        $currentChunk = $chunks->create($lazyLoadGroup, $jsLazyComponentsPath, $jsLazyFunctionsPath, $jsLazyResourcesPath);
                        $currentChunk->addComponent($buildItem->ComponentName);
                    }
                }

                $componentMeta['inputs'] = [];
                $preservedProps = [];
                foreach ($buildItem->Props as $prop => $propMetadata) {
                    $componentMeta['inputs'][$prop] = 1;
                    if (isset($propMetadata[Preserve::class])) {
                        $preservedProps[$prop] = 1;
                    }
                }
                if ($preservedProps) {
                    $componentMeta['preserve'] = $preservedProps;
                }
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
                if ($buildItem->StartUp) {
                    $startups[] = $buildItem->ComponentName;
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
                    if (!$buildItem->CustomJs) {
                        $publicJson[$buildItem->ComponentName]['nodes'] = TagItemConverter::getRaw($buildItem->RootTag);
                    }
                    // inline expressions
                    $exprComma = '';
                    foreach ($buildItem->RenderFunction->inlineExpressions as $code => [$expression, $arguments]) {
                        $funcArguments = implode(', ', ['_component', ...$arguments]);
                        $expressionsJs .= $exprComma . "    function ($funcArguments) { return $expression; }";
                        $exprComma = ',' . PHP_EOL;
                    }
                }
                $this->meta['components'][$buildItem->ComponentName] = $componentMeta;
                // if($buildItem->ComponentName === 'Login') {
                //     print_r($buildItem->JsOutput);
                // }
                // javascript
                if (!$buildItem->CustomJs) { // $buildItem->ComponentName !== 'BaseComponent'
                    // $lazyLoadGroups[$lazyLoadGroup]['path'] = ['components' => $jsLazyComponentsPath, 'functions' => $jsLazyFunctionsPath, 'resources' => $jsLazyResourcesPath];
                    $jsComponentPath = $currentChunk->jsComponentsPath . $d . $buildItem->ComponentName . '.js';
                    $jsComponentCode = '';
                    $comma = '';
                    $registerIncluded = false;
                    $additionalCode = "";
                    if ($lazyLoadGroup) {
                        $jsComponentCode .= 'import { register } from "../../../viewi/core/di/register";' . PHP_EOL;
                        $registerIncluded = true;
                    }

                    foreach ($buildItem->Uses as $importName => $useItem) {
                        if (!$useItem->Skip) {
                            if ($useItem->Type === UseItem::Class_) {
                                if ($importName === 'BaseComponent') {
                                    if ($lazyLoadGroup && isset($includedInMain[$importName])) {
                                        $additionalCode .= "var $importName = register.$importName;" . PHP_EOL;
                                    } else {
                                        $jsComponentCode .= 'import { BaseComponent } from "../../../viewi/core/component/baseComponent";' . PHP_EOL;
                                    }
                                } elseif (isset($this->components[$importName]) && $this->components[$importName]->CustomJs) {
                                    if (!$registerIncluded) {
                                        $jsComponentCode .= 'import { register } from "../../../viewi/core/di/register";' . PHP_EOL;
                                        $registerIncluded = true;
                                    }
                                    $additionalCode .= "var $importName = register.$importName;" . PHP_EOL;
                                } elseif (!isset($this->components[$importName]) || !$this->components[$importName]->Skip) {
                                    if ($lazyLoadGroup && isset($includedInMain[$importName])) {
                                        $additionalCode .= "var $importName = register.$importName;" . PHP_EOL;
                                    } else {
                                        $jsComponentCode .= "import { $importName } from \"./$importName\";" . PHP_EOL;
                                    }
                                }
                            } elseif ($useItem->Type === UseItem::Function) {
                                $currentChunk->functions[$importName] = true;
                                // TODO: collect function dependencies!!!
                                if ($lazyLoadGroup && isset($includedInMain[$importName])) {
                                    $additionalCode .= "var $importName = register.$importName;" . PHP_EOL;
                                } else {
                                    $jsComponentCode .= "import { $importName } from \"../functions/$importName\";" . PHP_EOL;
                                }
                            } elseif ($useItem->Type === UseItem::System) {
                                switch ($importName) {
                                    case 'ensureType': {
                                            if ($lazyLoadGroup && isset($includedInMain[$importName])) {
                                                $additionalCode .= "var $importName = register.$importName;" . PHP_EOL;
                                            } else {
                                                $jsComponentCode .= "import { ensureType } from \"../../../viewi/core/helpers/ensureType\";" . PHP_EOL;
                                            }
                                            break;
                                        }
                                    default: {
                                            break;
                                        }
                                }
                            }
                            $comma = PHP_EOL;
                            if (!$lazyLoadGroup) {
                                $includedInMain[$importName] = true;
                            }
                            if (!isset($includedInGroups[$importName])) {
                                $includedInGroups[$importName] = [];
                            }
                            $includedInGroups[$importName][] = $currentChunk->name;
                        }
                    }

                    if (!$lazyLoadGroup) {
                        $includedInMain[$buildItem->ComponentName] = true;
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
                        $currentChunk->componentsExport .= PHP_EOL . "    $expressionName,";
                        $expressionsImport .= ", $expressionName";
                    }

                    if ($lazyLoadGroup && isset($publicJson[$buildItem->ComponentName])) {
                        $expressionName = $buildItem->ComponentName . '_t';
                        $jsComponentCode .= $comma .
                            "export const $expressionName = { _t: 'template', name: '{$buildItem->ComponentName}', data: " .
                            json_encode(json_encode($publicJson[$buildItem->ComponentName], 0, 1024 * 32)) . ' };' . PHP_EOL;
                        $currentChunk->componentsExport .= PHP_EOL . "    $expressionName,";
                        $expressionsImport .= ", $expressionName";
                    }

                    $jsComponentCode .= PHP_EOL . 'export { ' . $buildItem->ComponentName . ' }';
                    file_put_contents($jsComponentPath, $jsComponentCode);
                    $currentChunk->componentsIndex .= "import { {$buildItem->ComponentName}$expressionsImport } from \"./{$buildItem->ComponentName}\";" . PHP_EOL;
                    $currentChunk->componentsExport .= PHP_EOL . "    {$buildItem->ComponentName},";
                } else {
                    $publicJson[$buildItem->ComponentName]['custom'] = 1;
                }
                if ($lazyLoadGroup) {
                    $lazyLoadGroups[$lazyLoadGroup]['public'][$buildItem->ComponentName] = $publicJson[$buildItem->ComponentName];
                    $publicJson[$buildItem->ComponentName] = ['lazy' => $lazyLoadGroup];
                }
            }
        }
        /** END COMPONENTS FOREACH **/
        $this->meta['publicConfig']['assets'] = $this->assetsPath;
        /** Post build actions **/
        $buildActionsModuleFile = $this->jsPath . $d . 'app' . $d . 'buildActions.mjs';
        $buildActionsList = [];
        foreach ($postBuild as $componentName => $buildAction) {
            if (isset($this->renderInvocations[$componentName])) {
                foreach ($this->renderInvocations[$componentName] as $staticProps) {
                    $actionItem = $buildAction->build($this, $staticProps);
                    if ($actionItem !== null) {
                        $buildActionsList[] = [
                            'type' => $actionItem->type,
                            'data' => $actionItem->data
                        ];
                        if ($actionItem->publicConfig !== null) {
                            $this->publicConfig = $this->array_merge_recursive($this->publicConfig, $actionItem->publicConfig);
                        }
                    }
                }
            }
        }
        $buildActionsContent = 'items: ' . json_encode($buildActionsList, JSON_PRETTY_PRINT) . ',' . PHP_EOL;
        $buildActionsContent = 'export const buildActions = {' . PHP_EOL . $buildActionsContent . '};';
        file_put_contents($buildActionsModuleFile, $buildActionsContent);
        /** END Post build actions **/

        $chunckBaseName = $this->appName === 'default' ? "viewi" : "viewi.{$this->appName}";
        $conponentsJsonPublicPath = $this->assetsPath . "/$chunckBaseName.json";
        $publicPath = $this->assetsPath . '/';
        $buildId = Helpers::randomString();
        $this->meta['assets'] = [
            'app' => $this->assetsPath . "/$chunckBaseName.js",
            'app-min' => $this->assetsPath . "/$chunckBaseName.min.js",
            'build-id' => $buildId,
            'minify' => $this->minifyJs,
            'append-version' => $this->appendVersion,
            'components' => $conponentsJsonPublicPath,
            'publicRootUrl' => $this->publicRootUrl,
            'publicRoot' => $this->publicRootPath,
            'publicAppRoot' => $this->publicPath
        ];
        if (count($startups) > 0) {
            $this->meta['startup'] = $startups;
        }
        $this->meta['publicConfig'] = $this->publicConfig;
        $this->meta['globals'] = $this->globalEntries;
        $componentsContent = '<?php' . PHP_EOL . 'return ' . var_export($this->meta, true) . ';';
        file_put_contents($this->buildPath . $d . 'components.php', $componentsContent); // TODO: make const or static helper
        // core PHP functions in JS
        // foreach ($this->usedFunctions as $functionName => $baseFunction) {
        //     if (!isset($includedInGroups[$functionName])) {
        //         $mainChunk->functions[$functionName] = true;
        //     }
        // }
        $viewiVersion = '2.0.0';
        $minifyStr = var_export($this->minifyJs, true);
        $appendVersionStr = var_export($this->appendVersion, true);
        $combineStr = var_export($this->combineJsJson, true);
        $resourcesIndexJs = 'export const resources = {' . PHP_EOL;
        $resourcesIndexJs .= "    componentsPath: '$conponentsJsonPublicPath'," . PHP_EOL;
        $resourcesIndexJs .= "    publicPath: '$publicPath'," . PHP_EOL;
        $resourcesIndexJs .= "    name: '{$this->appName}'," . PHP_EOL;
        $resourcesIndexJs .= "    minify: {$minifyStr}," . PHP_EOL;
        $resourcesIndexJs .= "    combine: {$combineStr}," . PHP_EOL;
        $resourcesIndexJs .= "    appendVersion: {$appendVersionStr}," . PHP_EOL;
        $resourcesIndexJs .= "    build: '$buildId'," . PHP_EOL;
        $resourcesIndexJs .= "    version: '$viewiVersion'," . PHP_EOL;
        $resourcesIndexJs .= '};';

        $publicJson['_meta'] = ['boolean' => $this->templateCompiler->getBooleanAttributesString()];
        $publicJson['_startup'] = $startups;
        $publicJson['_globals'] = $this->globalEntries;
        $publicJson['_routes'] = [];
        $publicJson['_routes'] = $publicRoutes;
        $publicJson['_config'] = $this->publicConfig;
        $publicJsonContent = json_encode($publicJson, 0, 1024 * 32);
        // components/index.js
        // functions/index.js
        foreach ($chunks->chunks as $chunkName => $chunk) {
            $isMain = $chunk->name === Chunk::MAIN;

            // components
            if (!$isMain) {
                $chunk->componentsIndex .= "import \"../../../modules/{$chunk->name}\";" . PHP_EOL;
                // server\viewi-app\js\modules\CustomJsPage\index.ts
                $modulesFolder = $this->jsPath . $d . 'modules' . $d . $chunk->name;
                $modulesFile = $modulesFolder . $d . 'index.ts';
                if (!file_exists($modulesFolder)) {
                    mkdir($modulesFolder, 0777, true);
                }
                if (!file_exists($modulesFile)) {
                    file_put_contents($modulesFile, "export const modules = {};");
                }
            }
            $chunk->componentsIndex .= PHP_EOL . "export const components = {{$chunk->componentsExport}";
            $chunk->componentsIndex .= ($chunk->componentsExport ? PHP_EOL . '};' : '};') . PHP_EOL;
            if ($isMain) {
                $templatesJSON = $this->combineJsJson ? json_encode($publicJsonContent) : '"{}"';
                $chunk->componentsIndex .= PHP_EOL . "export const templates = $templatesJSON;" . PHP_EOL;
            } else {
                $chunk->componentsIndex .= PHP_EOL . "window.ViewiApp.{$this->appName}.publish(\"$chunkName\", components);" . PHP_EOL;
            }
            file_put_contents($chunk->jsComponentsPath . $d . 'index.js', $chunk->componentsIndex);
            if ($isMain) {
                $chunk->distFileName = "viewi.js";
                $chunk->distFileMinName = "viewi.min.js";
                $chunk->publicFileName = "$chunckBaseName.js";
                $chunk->publicFileMinName = "$chunckBaseName.min.js";
                $chunk->distFileJsonName = "$chunckBaseName.json";
                $chunk->publicFileJsonName = "$chunckBaseName.json";
                file_put_contents($this->jsPath . $d . 'dist' . $d . $chunk->distFileJsonName, $publicJsonContent);
            } else {
                $chunk->distFileName = "viewi.$chunkName.js";
                $chunk->distFileMinName = "viewi.$chunkName.min.js";
                $chunk->publicFileName = "$chunckBaseName.$chunkName.js";
                $chunk->publicFileMinName = "$chunckBaseName.$chunkName.min.js";
                $lazyGroupEntry = "./app/$chunkName/components/index.js";
                $viewiLazyLoadGroupsModuleContent .= "    $chunkName: '$lazyGroupEntry'," . PHP_EOL;
            }

            // functions
            $functionsList = $chunk->functions; // need immutable since chunk will get changed
            foreach ($functionsList as $functionName => $_) {
                $this->collectChunkFunctions($chunk, $functionName);
            }

            $functionsIndexJs = '';
            $functionsExportList = '';
            foreach ($chunk->functions as $functionName => $_) {
                if ($isMain || !isset($mainChunk->functions[$functionName])) {
                    $baseFunction = $this->usedFunctions[$functionName];
                    $functionPath = $chunk->jsFunctionsPath . $d . $functionName . '.js';
                    $importDepsJs = '';
                    $registerDepsJs = '';
                    foreach ($baseFunction::getUses() as $requiredFunction) {
                        if ($isMain || !isset($mainChunk->functions[$requiredFunction])) {
                            $importDepsJs .= "import { $requiredFunction } from \"./$requiredFunction\";" . PHP_EOL;
                        } else {
                            $registerDepsJs .= "var $requiredFunction = register.$requiredFunction;" . PHP_EOL;
                        }
                    }

                    if ($registerDepsJs) {
                        $importDepsJs .= 'import { register } from "../../../viewi/core/di/register";' . PHP_EOL;
                        $registerDepsJs .= PHP_EOL;
                    }

                    if ($importDepsJs) {
                        $importDepsJs .= PHP_EOL;
                    }

                    $functionContent = $importDepsJs . $registerDepsJs . $baseFunction::getJs();
                    $functionContent .= PHP_EOL . "export { $functionName }";
                    file_put_contents($functionPath, $functionContent);
                    $functionsIndexJs .= "import { $functionName } from \"./{$functionName}\";" . PHP_EOL;
                    $functionsExportList .= PHP_EOL . "    {$functionName},";
                }
            }
            $functionsIndexJs .= PHP_EOL . "export const functions = {{$functionsExportList}";
            $functionsIndexJs .= $functionsExportList ? PHP_EOL . '};' : '};';
            file_put_contents($chunk->jsFunctionsPath . $d . 'index.js', $functionsIndexJs);
        }

        file_put_contents($jsResourcesPath . $d . 'index.js', $resourcesIndexJs);

        $viewiLazyLoadGroupsModuleContent = 'export const lazyGroups = {' . PHP_EOL . $viewiLazyLoadGroupsModuleContent . '};';
        file_put_contents($viewiLazyLoadGroupsModuleFile, $viewiLazyLoadGroupsModuleContent);



        // file_put_contents($this->jsPath . $d . 'dist' . $d . 'components.json', $publicJsonContent);
        // Run NPM command
        // TODO: no node mode (means no minfication and all the node features)

        if ($this->buildJsSourceCode) {
            $npmFolder = $this->jsPath . $d;
            $currentDir = getcwd();
            chdir($npmFolder);

            if (!file_exists($npmFolder . 'node_modules')) {
                $this->logs .= "Running NPM install command.." . PHP_EOL;
                $command = "npm --prefix $npmFolder install 2>&1";
                $lastLine = exec($command, $output, $result_code);
                $text = implode(PHP_EOL, $output ?? []) . PHP_EOL . $lastLine;
                if ($result_code !== 0) {
                    // Helpers::debug([$output, $lastLine, $result_code]);
                    throw new Exception("NPM build failed: code $result_code $text");
                }
                $this->logs .= "NPM output: " . PHP_EOL;
                $this->logs .= $text . PHP_EOL;
            }

            $this->logs .= "Running NPM build command.." . PHP_EOL;
            $command = "npm --prefix $npmFolder run build 2>&1";
            // $command = "npm run build 2>&1"; // test error
            $lastLine = exec($command, $output, $result_code);
            $text = implode(PHP_EOL, $output ?? []) . PHP_EOL . $lastLine;
            if ($result_code !== 0) {
                // Helpers::debug([$output, $lastLine, $result_code]);
                throw new Exception("NPM build failed: code $result_code $text");
            }
            $this->logs .= "NPM output: " . PHP_EOL;
            $this->logs .= $text . PHP_EOL;
            // TODO: configurable paths
            // TODO: configurable minify
            chdir($currentDir);
            $this->logs .= "Moving assets to public folder.." . PHP_EOL;
            foreach ($chunks->chunks as $chunk) {
                $distJsFile = $this->jsPath . $d . 'dist' . $d . $chunk->distFileName;
                if (!file_exists($distJsFile)) {
                    throw new Exception("Could not find Viewi build file at $distJsFile.");
                }
                copy($distJsFile, $this->publicPath . $d . $chunk->publicFileName);

                $distJsFileMin = $this->jsPath . $d . 'dist' . $d . $chunk->distFileMinName;
                if (!file_exists($distJsFileMin)) {
                    throw new Exception("Could not find Viewi build file at $distJsFileMin.");
                }
                copy($distJsFileMin, $this->publicPath . $d . $chunk->publicFileMinName);
                file_put_contents("$distJsFileMin.gz", gzencode(file_get_contents($distJsFileMin), 5));
                if ($chunk->distFileJsonName) {
                    copy($this->jsPath . $d . 'dist' . $d . $chunk->distFileJsonName, $this->publicPath . $d . $chunk->publicFileJsonName);
                }
            }
            $this->logs .= "Ready!" . PHP_EOL;
        }
        if (!empty($this->assetsSourcePath)) {
            Helpers::copyAll($viewiDistAssetsPath . $d, $this->publicPath . $d);
        }
    }

    private function collectChunkFunctions(Chunk $chunk, string $functionName): void
    {
        $baseFunction = $this->usedFunctions[$functionName];
        foreach ($baseFunction::getUses() as $requiredFunction) {
            if (!isset($chunk->functions[$requiredFunction])) {
                $chunk->functions[$requiredFunction] = true;
                $this->collectChunkFunctions($chunk, $requiredFunction);
            }
        }
    }

    /**
     * 
     * @param ReflectionClass $reflectionClass 
     * @return array 
     */
    private function getProps(BuildItem $buildItem): array
    {
        $reflectionClass = $buildItem->ReflectionClass;
        $inputs = [];
        $props = $reflectionClass->getProperties(ReflectionProperty::IS_PUBLIC);
        if (count($props) > 0) {
            foreach ($props as $propertyInfo) {
                $attributeMetadata = [];
                $attributes = $propertyInfo->getAttributes();
                $propName = $propertyInfo->getName();
                if ($attributes) {
                    foreach ($attributes as $attribute) {
                        $attributeClass = $attribute->getName();
                        $attributeMetadata[$attributeClass] = $attribute;
                        if ($attributeClass === GlobalEntry::class) {
                            $this->globalEntries[$propName] = $reflectionClass->getShortName();
                        } elseif ($attributeClass === Inject::class) {
                            /**
                             * @var Inject $injectAttribute
                             */
                            $injectAttribute = $attribute->newInstance();
                            $argumentClass = $propertyInfo->getType() && !$propertyInfo->getType()->isBuiltin()
                                ? new ReflectionClass($propertyInfo->getType()->getName())
                                : null; // check if class exists
                            if ($argumentClass !== null) {
                                $buildItem->DiProps[$propName] = ['name' => $argumentClass->getShortName(), 'di' => $injectAttribute->scope];
                            }
                        }
                    }
                }
                $inputs[$propName] = $attributeMetadata;
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
                $attributeMetadata = [];
                $attributes = $method->getAttributes();
                if ($attributes) {
                    foreach ($attributes as $attribute) {
                        $attributeClass = $attribute->getName();
                        $attributeMetadata[$attributeClass] = $attribute;
                        if ($attributeClass === GlobalEntry::class) {
                            $this->globalEntries[$method->name] = $reflectionClass->getShortName();
                        }
                    }
                }
                $list[$method->name] = $attributeMetadata;
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

                    $attributes = $argument->getAttributes();
                    foreach ($attributes as $attribute) {
                        $attributeClass = $attribute->getName();
                        switch ($attributeClass) {
                            case Inject::class: {
                                    /**
                                     * @var Inject $injectAttribute
                                     */
                                    $injectAttribute = $attribute->newInstance();
                                    $dependencies[$argumentName]['di'] = $injectAttribute->scope;
                                    break;
                                }
                            default: {
                                    break;
                                }
                        }
                    }
                }
            }
        }
        return $dependencies;
    }

    /**
     * https://github.com/RikudouSage/ArrayMergeRecursive/tree/master
     * @param array<mixed> $array1
     * @param array<mixed> $array2
     * @param array<mixed> ...$arrays
     *
     * @return array<mixed>
     */
    public function array_merge_recursive(array $array1, array $array2, array ...$arrays): array
    {
        array_unshift($arrays, $array2);
        array_unshift($arrays, $array1);

        $merged = [];
        while ($arrays) {
            $array = array_shift($arrays);
            assert(is_array($array));
            if (!$array) {
                continue;
            }

            foreach ($array as $key => $value) {
                if (is_string($key)) {
                    if (is_array($value) && array_key_exists($key, $merged) && is_array($merged[$key])) {
                        $merged[$key] = $this->array_merge_recursive($merged[$key], $value);
                    } else {
                        $merged[$key] = $value;
                    }
                } else {
                    $merged[] = $value;
                }
            }
        }

        return $merged;
    }
}
