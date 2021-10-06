<?php

namespace Viewi;

use \ReflectionClass;
use \ReflectionProperty;
use \ReflectionNamedType;
use \Exception;
use ReflectionException;
use Viewi\Components\Interfaces\IMiddleware;
use \Viewi\Routing\Route;

class PageEngine
{

    /**
     * Location of components source code
     */
    const SOURCE_DIR = 'SOURCE_DIR';

    /**
     * Target directory of compiled php components
     */
    const SERVER_BUILD_DIR = 'SERVER_BUILD_DIR';

    /**
     * Public root folder of application (location of index.php)
     */
    const PUBLIC_ROOT_DIR = 'PUBLIC_ROOT_DIR';

    /**
     * Target directory of compiled public assets (javascripts, etc.)
     */
    const PUBLIC_BUILD_DIR = 'PUBLIC_BUILD_DIR';

    /**
     * Url path of compiled public assets (javascripts, etc.), for ex: /build or /public/build
     */
    const PUBLIC_URL_PATH = 'PUBLIC_URL_PATH';

    /**
     * combine all viewi scripts into one, use in production.
     */
    const COMBINE_JS = 'COMBINE_JS';

    /**
     * enable scripts minification, use in production.
     */
    const MINIFY = 'MINIFY';

    /**
     * true if you are in developing mode.
     * All components will be compiled as soon as request occurs. 
     * Default: true.
     */
    const DEV_MODE = 'DEV_MODE';

    /**
     * true if you want to render into variable, otherwise - echo output, Default: true.
     */
    const RETURN_OUTPUT = 'RETURN_OUTPUT';



    private string $sourcePath;
    private string $buildPath;
    private string $publicRootPath;
    private string $publicBuildDir;
    private string $publicBuildPath;
    private ?PageTemplate $latestPageTemplate = null;
    private array $slotCounterMap;
    /** @var ComponentInfo[] */
    private array $components;
    private array $templateVersions;
    private ?BaseComponent $currentComponentInstance;
    /** @var \ReflectionClass[] */
    private array $componentReflectionTypes;
    /** @var mixed[] */
    private array $tokens;
    /** @var string[] */
    private array $compiledJs;
    /** @var array<string,array<string,array<string,bool>>> */
    private array $componentDependencies;
    /** @var PageTemplate[] */
    private array $templates;

    /** @var string<string, string> */
    private array $reservedTags;
    private string $indentation = '    ';

    private string $reservedTagsString = 'html,body,base,head,link,meta,style,title,' .
        'address,article,aside,footer,header,h1,h2,h3,h4,h5,h6,hgroup,nav,section,' .
        'div,dd,dl,dt,figcaption,figure,picture,hr,img,li,main,ol,p,pre,ul,' .
        'a,b,abbr,bdi,bdo,br,cite,code,data,dfn,em,i,kbd,mark,q,rp,rt,rtc,ruby,' .
        's,samp,small,span,strong,sub,sup,time,u,var,wbr,area,audio,map,track,video,' .
        'embed,object,param,source,canvas,script,noscript,del,ins,' .
        'caption,col,colgroup,table,thead,tbody,td,th,tr,' .
        'button,datalist,fieldset,form,input,label,legend,meter,optgroup,option,' .
        'output,progress,select,textarea,' .
        'polygon,polyline,details,dialog,menu,menuitem,summary,' .
        'content,element,shadow,template,blockquote,iframe,tfoot,' .
        'svg,animate,circle,clippath,cursor,defs,desc,ellipse,filter,font-face,' .
        'foreignObject,g,glyph,image,line,marker,mask,missing-glyph,path,pattern,' .
        'rect,switch,symbol,text,textpath,tspan,use,view,template,slot,slotcontent';

    /** @var array<string,string> */
    private array $selfClosingTags;

    private string $voidTagsString = 'area,base,br,col,embed,hr,img,input,link,meta,param,source,track,wbr';

    /** @var array<string,string> */
    private array $voidTags;

    private string $selfClosingTagsString = 'area,base,br,col,command,embed,hr' .
        ',img,input,keygen,link,menuitem,meta,param,source,track,wbr';

    /** @var array<string,string> */
    private array $booleanAttributes;
    private string $booleanAttributesString = 'async,autofocus,autoplay,checked,controls,' .
        'default,defer,disabled,formnovalidate,hidden,ismap,itemscope,loop,' .
        'multiple,muted,nomodule,novalidate,open,readonly,required,reversed,' .
        'selected';

    private bool $lastLineIsSpace = false;
    private bool $extraLine = false;
    private TagItem $previousItem;
    private bool $development;
    private array $componentArguments = [];
    private array $componentScopeVariables = [];
    private bool $compiled = false;
    private bool $waitingComponents = true;
    private JsTranslator $expressionsTranslator;
    private array $requestedIncludes = [];
    /**
     * 
     * @var bool true: return string, false: echo
     */
    private bool $renderReturn;
    private string $_CompileComponentName = '$_component';
    private string $_CompileExpressionPrefix;
    private string $_CompileExpressionJsPrefix;
    private bool $enableMinificationAndGzipping;
    private bool $combineJs;
    /**
     * 
     * @var array<string,object>
     */
    private array $Dependencies = [];
    private int $forIterationKey = 0;
    private array $config;
    private array $_slots = [];
    public static ?array $publicConfig = null;

    public function __construct(array $config, ?array $publicConfig = null)
    {
        $this->config = $config;
        self::$publicConfig = $publicConfig;
        $this->sourcePath = $config[self::SOURCE_DIR]; // $sourcePath;
        $this->buildPath = $config[self::SERVER_BUILD_DIR]; // $buildPath;
        $this->publicRootPath = $config[self::PUBLIC_ROOT_DIR]; // $publicRootPath;
        $this->publicBuildDir = $config[self::PUBLIC_BUILD_DIR]; // $publicBuildPath;
        $this->publicBuildPath = $this->publicRootPath . $this->publicBuildDir;
        $this->renderReturn = $config[self::RETURN_OUTPUT] ?? true; // $return;
        $this->components = [];
        $this->tokens = [];
        $this->templates = [];
        $this->development =  $config[self::DEV_MODE] ?? true; // $development;
        $this->reservedTags = array_flip(explode(',', $this->reservedTagsString));
        $this->voidTags = array_flip(explode(',', $this->voidTagsString));
        $this->selfClosingTags = array_flip(explode(',', $this->selfClosingTagsString));
        $this->booleanAttributes = array_flip(explode(',', $this->booleanAttributesString));
    }

    /**
     * 
     * @param string $component 
     * @return string|void
     * @throws ReflectionException 
     * @throws Exception Component is missing
     */
    function render(string $component, array $params = [])
    {
        $component = strpos($component, '\\') !== false ?
            substr(strrchr($component, "\\"), 1)
            : $component;
        if ($this->development) {
            set_time_limit(5);
            $this->compile($component);
        } else {
            if ($this->waitingComponents) {
                $this->waitingComponents = false;
                // include component infos
                $componentsPath = $this->buildPath . DIRECTORY_SEPARATOR . 'components.php';
                include_once $componentsPath;
                ReadComponentsInfo($this);
            }
        }

        if (!isset($this->components[$component])) {
            throw new Exception("Component {$component} is missing!");
        }
        $content = $this->renderComponent($component, $params, null, [], []);
        return $content;
    }

    function removeDirectory($path, $removeRoot = false)
    {
        $files = glob($path . '/*');
        foreach ($files as $file) {
            is_dir($file) ? $this->removeDirectory($file, true) : unlink($file);
        }
        if ($removeRoot) {
            rmdir($path);
        }
        return;
    }

    public function isTag(string $name): bool
    {
        return isset($this->reservedTags[$name]);
    }

    /**
     * 
     * @param string $filename 
     * @return string 
     */
    private function getClassNameByToken(string $filename): string
    {
        $tokens = token_get_all(file_get_contents($filename), TOKEN_PARSE);
        $className = '';
        $nextStringIsClass = false;
        foreach ($tokens as &$token) {
            if (is_int($token[0])) {
                $token[] = token_name($token[0]);
                if ($token[0] == T_CLASS) {
                    $nextStringIsClass = true;
                }
                if ($nextStringIsClass && $token[0] == T_STRING) {
                    if (empty($className)) {
                        $className = $token[1];
                    }
                    $nextStringIsClass = false;
                }
            }
        }
        return $className;
    }

    /**
     * 
     * @param string $baseClass
     * @return array<string, ReflectionClass>
     */
    private function getClasses(?string $baseClass, ?string $path = null): array
    {
        $children  = array();
        $types = get_declared_classes();
        // $this->debug($types);
        foreach ($types as $class) {
            $rf = new ReflectionClass($class);
            if ($baseClass !== null && $path !== null) {
                if (is_subclass_of($class, $baseClass) && strpos($rf->getFileName(), $path) === 0) {
                    $children[$class] = $rf;
                }
            } else if ($baseClass !== null && is_subclass_of($class, $baseClass)) {
                $children[$class] = $rf;
            } else if ($path !== null && strpos($rf->getFileName(), $path) === 0) {
                $children[$class] = $rf;
            }
        }
        return $children;
    }

    function getRelativeBuildPath(string $fullPath): string
    {
        return str_replace($this->buildPath, '', $fullPath);
    }

    function getRelativeSourcePath(string $fullPath): string
    {
        return str_replace($this->sourcePath, '', $fullPath);
    }

    function updateComponentPath(ComponentInfo $componentInfo, string $fullPath): void
    {
        $componentInfo->Fullpath = $this->getRelativeSourcePath($fullPath);
        $componentInfo->Relative = $componentInfo->Fullpath !== $fullPath;
    }

    /**
     * 
     * @param ReflectionClass $reflectionClass 
     * @return void 
     */
    function buildDependencies(ReflectionClass $reflectionClass): void
    {
        $name = $reflectionClass->getShortName();
        if (!isset($this->components[$name])) {
            $componentInfo = new ComponentInfo();
            $componentInfo->Name = $name;
            $componentInfo->Namespace = $reflectionClass->getNamespaceName();
            $componentInfo->IsComponent = false;
            $componentInfo->HasInit = $reflectionClass->hasMethod('__init');
            $componentInfo->HasMounted = $reflectionClass->hasMethod('__mounted');
            $componentInfo->HasBeforeMount = $reflectionClass->hasMethod('__beforeMount');
            $this->updateComponentPath($componentInfo, $reflectionClass->getFileName());
            $this->components[$name] = $componentInfo;
            $dependencies = $this->getDependencies($reflectionClass, $componentInfo->HasInit);
            if (!empty($dependencies)) {
                $componentInfo->Dependencies = $dependencies;
            }
        }
    }

    /** @return PageTemplate[] */
    public function getTemplates(): array
    {
        return $this->templates;
    }

    /**
     * 
     * @param ReflectionClass $reflectionClass 
     * @return array<array,string]>>
     */
    function getDependencies(ReflectionClass $reflectionClass): array
    {
        $dependencies = [];
        $constructor = $reflectionClass->hasMethod('__init')
            ? $reflectionClass->getMethod('__init')
            : $reflectionClass->getConstructor();
        if ($constructor !== null) {
            $construcorArgs = $constructor->getParameters();
            if (!empty($construcorArgs)) {

                foreach ($construcorArgs as $argument) {
                    $argumentName = $argument->name;
                    if ($argument->hasType()) {
                        /** @var ReflectionNamedType $namedType */
                        $namedType = $argument->getType();
                        if ($namedType instanceof ReflectionNamedType) {
                            // $this->debug($namedType->getName());
                            // $this->debug($argument->getClass());
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
                            if (!is_null($argumentClass)) {
                                $this->buildDependencies($argumentClass);
                                $className = $argumentClass->getShortName();
                                $this->compileToJs($argumentClass);
                            }
                        }
                    } else {
                        throw new Exception("Argument '$argumentName' in class" .
                            "{$reflectionClass->name}' can`t be resolved without type in {$reflectionClass->getFileName()}.");
                    }
                }
            }
        }
        return $dependencies;
    }

    function compileToJs(ReflectionClass $reflectionClass): void
    {
        $className = $reflectionClass->getShortName();
        if (!isset($this->compiledJs[$className])) {
            $phpSourceFileName = $reflectionClass->getFileName();
            $pathinfo = pathinfo($phpSourceFileName);
            $pathWOext = $pathinfo['dirname'] . DIRECTORY_SEPARATOR . $pathinfo['filename'];
            $jsSourceMinFileName = $pathWOext . '.min.js';
            $jsSourceFileName = $pathWOext . '.js';
            $jsCode = '';
            if ($this->enableMinificationAndGzipping && file_exists($jsSourceMinFileName)) {
                $jsCode = file_get_contents($jsSourceMinFileName) . PHP_EOL . PHP_EOL;
            } else if (file_exists($jsSourceFileName)) {
                $jsCode = file_get_contents($jsSourceFileName) . PHP_EOL . PHP_EOL;
            } else {
                $raw = file_get_contents($phpSourceFileName);
                $translator = new JsTranslator($raw);
                $jsCode = $translator->convert();
                $this->requestedIncludes = array_merge($this->requestedIncludes, $translator->getRequestedIncludes());
                $this->componentDependencies += $translator->getVariablePaths();
                $usingList = $translator->getUsingList();
                foreach ($usingList as $useClassName => $boolTrue) {
                    $refClass = new ReflectionClass($useClassName);
                    if (
                        $refClass->isUserDefined()
                        && !$refClass->isInternal()
                        && $refClass->isInstantiable()
                        && $refClass->getFileName()
                    ) {
                        $this->compileToJs($refClass);
                    }
                }
            }
            // $this->debug($className);
            // $this->debug($jsCode);            
            $this->compiledJs[$className] = $jsCode;
        }
    }

    /**
     * 
     * @param string|null $initialComponent fallback component
     * @return void 
     * @throws ReflectionException 
     * @throws Exception 
     */
    function compile(string $initialComponent = null): void
    {
        if ($this->compiled) {
            return;
        }
        $this->enableMinificationAndGzipping = $this->config[self::MINIFY] ?? false;
        $this->combineJs = $this->config[self::COMBINE_JS] ?? false;
        $this->slotCounterMap = [];
        $this->_CompileExpressionPrefix = $this->_CompileComponentName . '->';
        $this->_CompileExpressionJsPrefix = str_replace('$', '', $this->_CompileComponentName . '.');
        $this->expressionsTranslator = new JsTranslator('');
        $this->expressionsTranslator->setOuterScope();
        $this->compiledJs = [];
        $this->componentDependencies = [];
        $this->compiled = true;
        $this->templateVersions = [];
        $this->sourcePath = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $this->sourcePath);
        $this->removeDirectory($this->buildPath);
        $this->removeDirectory($this->publicBuildPath);
        $viewiComponentsPath = __DIR__ . '/Components';
        $viewiComponentsPath = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $viewiComponentsPath);
        if (!file_exists($this->publicBuildPath)) {
            mkdir($this->publicBuildPath, 0777, true);
        }
        $pages = $this->getDirContents($this->sourcePath)
            + $this->getDirContents($viewiComponentsPath);
        // $this->debug($pages);
        foreach (array_keys($pages) as $filename) {
            $pathinfo = pathinfo($filename);
            if ($pathinfo['extension'] === 'php') {
                include_once $filename;
            }
        }
        $types = $this->getClasses(BaseComponent::class, $this->sourcePath)
            + $this->getClasses(BaseComponent::class, $viewiComponentsPath);
        // $this->debug($this->sourcePath);
        // $this->debug($types);
        foreach ($types as $filename => &$reflectionClass) {
            // $this->debug('Path: '.$filename);
            $versionComponentInfo = new ComponentInfo();
            $className = $reflectionClass->getShortName();
            $filename = $reflectionClass->getFileName();
            $dependencies = $this->getDependencies($reflectionClass);
            if (!empty($dependencies)) {
                $versionComponentInfo->Dependencies = $dependencies;
            }
            $pathinfo = pathinfo($filename);
            $pathWOext = $pathinfo['dirname'] . DIRECTORY_SEPARATOR . $pathinfo['filename'];
            $templatePath = $pathWOext . '.html';
            $versionComponentInfo->IsComponent = true;
            $this->updateComponentPath($versionComponentInfo, $filename);
            if (isset($pages[$templatePath])) {
                $versionComponentInfo->TemplatePath = $this->getRelativeSourcePath($templatePath);
            }
            $versionComponentInfo->Name = $className;
            $versionComponentInfo->Namespace = $reflectionClass->getNamespaceName();
            $versionComponentInfo->ComponentName = $className;
            $versionComponentInfo->Tag = $className;
            $versionComponentInfo->HasInit = $reflectionClass->hasMethod('__init');
            $versionComponentInfo->HasMounted = $reflectionClass->hasMethod('__mounted');
            $versionComponentInfo->HasBeforeMount = $reflectionClass->hasMethod('__beforeMount');
            $versionComponentInfo->HasVersions = $reflectionClass->hasMethod('__version');
            if (!empty($className)) {
                $this->components[$className] = $versionComponentInfo;
                $this->componentReflectionTypes[$className] = $reflectionClass;
            }
            $this->compileToJs($reflectionClass);
        }
        $types = $this->getClasses(null, $this->sourcePath);
        foreach ($types as $filename => &$reflectionClass) {
            $this->buildDependencies($reflectionClass);
        }
        //$this->debug($this->sourcePath);
        //$this->debug($this->buildPath);
        $publicJson = [];
        foreach ($this->components as $className => &$componentInfo) {
            if (isset($componentInfo->HasVersions) && $componentInfo->HasVersions) {
                $this->templates[$className] = $this->compileTemplate($componentInfo); // compile just for selectors
                // print_r($this->templates[$className]);
                continue; // has multiple templates based on input
            }
            if ($componentInfo->IsComponent) {
                $this->templates[$className] = $this->compileTemplate($componentInfo);
                // $this->debug('HomePage now (compile): ' . $this->templates['HomePage']->RootTag->getChildren()[0]->Content);
                $this->build($this->templates[$className]);
                $this->save($this->templates[$className]);
            }
            if (!isset($componentInfo->IsSlot) || !$componentInfo->IsSlot) {
                // $publicJson[$className] = $componentInfo;
                $publicJson[$className] = [];
                if (isset($componentInfo->Dependencies)) {
                    $publicJson[$className]['dependencies'] = [];
                    foreach ($componentInfo->Dependencies as $argumentName => $argumentInfo) {
                        $publicJson[$className]['dependencies'][] = array_merge(['argName' => $argumentName], $argumentInfo);
                    }
                };
                if ($componentInfo->IsComponent) {
                    $publicJson[$className]['nodes'] = $this->templates[$className]->RootTag->getRaw();
                } else {
                    $publicJson[$className]['service'] = true;
                }
                if ($componentInfo->HasInit)
                    $publicJson[$className]['init'] = true;
            }
        }

        foreach ($this->templateVersions as $className => $versions) {
            $versionComponentInfo = $this->components[$className];
            $templates = [];
            $numberContext = 0;
            $publicJson[$className] = [];
            $publicJson[$className] = [];
            if (isset($versionComponentInfo->Dependencies)) {
                $publicJson[$className]['dependencies'] = [];
                foreach ($versionComponentInfo->Dependencies as $argumentName => $argumentInfo) {
                    $publicJson[$className]['dependencies'][] = array_merge(['argName' => $argumentName], $argumentInfo);
                }
            };
            $publicJson[$className]['hasVersions'] = true;
            $publicJson[$className]['versions'] = [];
            if ($versionComponentInfo->HasInit)
                $publicJson[$className]['init'] = true;

            foreach ($versions as $arguments) {
                // init instance
                $instance = $this->resolve($versionComponentInfo, false, [], true);
                $this->currentComponentInstance = $instance;
                // $this->debug($instance);
                // $this->debug($arguments);
                foreach ($arguments as $key => $phpString) {
                    if (isset($versionComponentInfo->Inputs[$key])) {
                        $instance->{$key} = eval("return $phpString;");
                    }
                }
                // $this->debug($instance);
                if ($versionComponentInfo->IsComponent) {
                    $template = $this->compileTemplate($versionComponentInfo);
                    $version = $instance->__version();
                    // $this->debug($version);
                    $templateKey = '_v' . (++$numberContext);
                    $templates[$version] = [
                        'key' => $templateKey,
                    ];
                    // $this->debug('HomePage now (compile): ' . $this->templates['HomePage']->RootTag->getChildren()[0]->Content);
                    $this->build($template, $templateKey);
                    $this->save($template, $templateKey);
                    $templates[$version]['BuildPath'] = $template->ComponentInfo->BuildPath;
                    $templates[$version]['RenderFunction'] = $template->ComponentInfo->RenderFunction;
                    $publicJson[$className]['versions'][$version] = $template->RootTag->getRaw();
                }
                $this->currentComponentInstance = null;
            }
            unset($versionComponentInfo->BuildPath);
            unset($versionComponentInfo->RenderFunction);
            $versionComponentInfo->Versions = $templates;
        }
        $thisRoot = __DIR__ . DIRECTORY_SEPARATOR;

        // mate info
        $publicJson['_meta'] = ['tags' => $this->reservedTagsString, 'boolean' => $this->booleanAttributesString];
        $routes = Route::getRoutes();
        if ($initialComponent && count($routes) === 0) {
            Route::get('*', $initialComponent);
            $routes = Route::getRoutes();
        }
        $publicJson['_routes'] = [];
        foreach ($routes as $route) {
            if (!is_callable($route->action)) {
                $asocRoute = (array)$route;
                unset($asocRoute['action']);
                $publicJson['_routes'][] = $asocRoute;
            }
        }
        $publicJson['_config'] = self::$publicConfig;
        // $this->debug($this->templates);
        $componentsPath = $this->buildPath . DIRECTORY_SEPARATOR . 'components.php';
        $content = var_export(json_decode(json_encode($this->components), true), true);
        $componentsInfoTemplate = $thisRoot . 'ComponentsInfoTemplate.php';
        $templateContent = file_get_contents($componentsInfoTemplate);
        $parts = explode("//#content", $templateContent, 2);
        $content = $parts[0] . '$pageEngine->setComponentsInfo(' . $content . ');' . $parts[1]; // $pageEngine
        file_put_contents($componentsPath, $content);


        $combined = '';

        $publicFilePath = $this->publicBuildPath . DIRECTORY_SEPARATOR . 'components.json';
        $publicJsFilePath = $this->publicBuildPath . DIRECTORY_SEPARATOR
            . ($this->enableMinificationAndGzipping ? 'bundle.min.js' : 'bundle.js');
        // $publicMinJsFilePath = $this->publicBuildPath . DIRECTORY_SEPARATOR . 'bundle.min.js';

        $publicAppJsFilePath = $this->publicBuildPath . DIRECTORY_SEPARATOR
            . ($this->enableMinificationAndGzipping ? 'app.min.js' : 'app.js');
        // $publicAppMiniJsFilePath = $this->publicBuildPath . DIRECTORY_SEPARATOR . 'app.min.js';
        $copyright = file_get_contents($thisRoot . 'js/copyright.js');


        $publicJsonContent = json_encode($publicJson, 0, 1024);
        $publicJsonStringContent = json_encode($publicJsonContent);
        $publicJsonContentJs = PHP_EOL . "ViewiPages = $publicJsonStringContent;" . PHP_EOL . PHP_EOL;

        $jsContentToInclude = '';
        foreach ($this->requestedIncludes as $path) {
            $jsContentToInclude .= file_get_contents($path) . PHP_EOL . PHP_EOL;
        }
        $publicBundleJs = $jsContentToInclude . implode('', array_values($this->compiledJs));

        $appJsContent = $copyright
            . ($this->enableMinificationAndGzipping ?
                file_get_contents($thisRoot . 'js/router.min.js') :
                file_get_contents($thisRoot . 'js/router.js'))
            . ($this->combineJs ? $publicBundleJs . $publicJsonContentJs : '')
            . PHP_EOL . 'var VIEWI_PATH = "' . $this->config[PageEngine::PUBLIC_BUILD_DIR] . '";'
            . PHP_EOL . 'var VIEWI_VERSION = "' . ($this->development ? '' : '?v=' . date('ymdHis')) . '";' . PHP_EOL
            . ($this->enableMinificationAndGzipping ?
                file_get_contents($thisRoot . 'js/app.min.js') :
                file_get_contents($thisRoot . 'js/app.js'));

        $publicBundleJs = $copyright . $publicBundleJs;

        file_put_contents($publicAppJsFilePath, $appJsContent);
        file_put_contents($publicFilePath, $publicJsonContent);
        file_put_contents($publicJsFilePath, $publicBundleJs);

        file_put_contents($publicAppJsFilePath . '.gz', gzencode($appJsContent, 5));
        file_put_contents($publicJsFilePath . '.gz', gzencode($publicBundleJs, 5));
        file_put_contents($publicFilePath . '.gz', gzencode($publicJsonContent, 5));
        file_put_contents($publicFilePath . '.gz', gzencode($publicJsonContent, 5));
        //$this->debug($this->components);
    }

    function minify($js)
    {
        return $js; // TODO: implement js minification without API
    }

    function minifyByAPI($js): string
    {
        $minified = $js;
        try {
            // setup the URL and read the JS from a file
            $url = 'https://javascript-minifier.com/raw';

            // init the request, set various options, and send it
            $ch = curl_init();

            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_HTTPHEADER => ["Content-Type: application/x-www-form-urlencoded"],
                CURLOPT_POSTFIELDS => http_build_query(["input" => $js])
            ]);
            $minified = curl_exec($ch);
            curl_close($ch);
        } catch (Exception $exc) {
            $this->debug($exc);
        }
        return $minified;
    }

    /**
     * 
     * @param ComponentInfo[] $componentsInfo 
     * @return void 
     */
    function setComponentsInfo(array $componentsInfo): void
    {
        foreach ($componentsInfo as &$item) {
            $componentInfo = new ComponentInfo();
            $componentInfo->fromArray($item);
            $this->components[$componentInfo->Name] = $componentInfo;
        }
    }

    function save(PageTemplate &$pageTemplate, string $templateKey = '')
    {
        $buildPath = $this->buildPath;
        if ($pageTemplate->ItsSlot) {
            $buildPath .= DIRECTORY_SEPARATOR . '_slots';
        }
        $buildFilePath = $pageTemplate->ComponentInfo->Relative ?
            str_replace($this->sourcePath, $buildPath, $this->sourcePath . $this->getRelativeSourcePath($pageTemplate->Path))
            : $buildPath . DIRECTORY_SEPARATOR . str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $pageTemplate->ComponentInfo->Namespace) . DIRECTORY_SEPARATOR . $pageTemplate->ComponentInfo->Name;
        $pathinfo = pathinfo($buildFilePath);
        // $this->debug($pageTemplate);
        // $this->debug($pathinfo);
        if (!file_exists($pathinfo['dirname'])) {
            mkdir($pathinfo['dirname'], 0777, true);
        }
        $pathWOext = $pathinfo['dirname'] . DIRECTORY_SEPARATOR . $pathinfo['filename'];
        $phpPath = $pathWOext . $templateKey . '.php';
        file_put_contents($phpPath, $pageTemplate->PhpHtmlContent);
        $pageTemplate->ComponentInfo->BuildPath = $this->getRelativeBuildPath($phpPath);
        $pageTemplate->RootTag->cleanParents();
    }

    function build(PageTemplate &$pageTemplate, string $templateKey = ''): void
    {
        $this->previousItem = new TagItem();
        $this->previousItem->Type = new TagItemType(TagItemType::TextContent);
        $moduleTemplatePath = __DIR__ . DIRECTORY_SEPARATOR . 'ComponentModuleTemplate.php';
        $moduleContent = file_get_contents($moduleTemplatePath);
        $parts = explode("//#content", $moduleContent, 2);
        $html = $parts[0];
        $renderFunction = "Render{$pageTemplate->ComponentInfo->Name}$templateKey";
        $html = str_replace('BaseComponent $', $pageTemplate->ComponentInfo->Namespace . '\\' . $pageTemplate->ComponentInfo->ComponentName . ' $', $html);
        $html = str_replace('RenderFunction', $renderFunction, $html);

        $scopeArguments = implode(', ', $this->componentArguments);
        if ($scopeArguments) {
            $scopeArguments = ', ' . $scopeArguments . ', ...$scope';
        } else {
            $scopeArguments = ', ...$scope';
        }
        $html = str_replace('/** scope*/', $scopeArguments, $html);
        // 
        if ($this->renderReturn) {
            $html .= PHP_EOL . $this->indentation . "\$_content = '';" . PHP_EOL;
        } else {
            $html .= '?>';
            if ($this->lastLineIsSpace) {
                $html .= PHP_EOL;
            }
        }
        $this->buildInternal($pageTemplate, $html);
        if ($this->renderReturn) {
            $html .= PHP_EOL . $this->indentation . "return \$_content;" . PHP_EOL;
        } else {
            $html .= '<?php';
        }
        $html .= $parts[1];
        $pageTemplate->PhpHtmlContent = $html;
        $pageTemplate->ComponentInfo->RenderFunction = $renderFunction;
        //$this->debug(htmlentities($html));
        //$this->debug(json_encode($pageTemplate, JSON_PRETTY_PRINT));
    }

    private function buildInternal(PageTemplate &$pageTemplate, string &$html): void
    {
        $previousPageTemplate = $this->latestPageTemplate;
        $this->latestPageTemplate = $pageTemplate;
        $codeToAppend = '';
        foreach ($pageTemplate->RootTag->getChildren() as &$tag) {
            $this->buildTag($tag, $html, $codeToAppend);
        }
        if ($codeToAppend) {
            if ($this->renderReturn) {
                $html .= PHP_EOL . $this->indentation . "\$_content .= " .
                    var_export($codeToAppend, true) . ";";
            } else {
                $html .= $codeToAppend;
            }
        }
        $this->latestPageTemplate = $previousPageTemplate;
    }

    function convertExpressionToCode(string $expression, array $reserved = []): string
    {
        $keywordsList = $this->expressionsTranslator->getKeywords($expression);
        $keywords = $keywordsList[0];
        $spaces = $keywordsList[1];
        $newExpression = '';
        // $this->debug($expression);
        // $this->debug($keywordsList);
        $count = count($keywords);
        $newVariables = false;
        foreach ($keywords as $i => $keyword) {
            if ($keyword === 'as') {
                $newVariables = true;
            }
            if (isset($reserved[$keyword])) {
                $newExpression .= $spaces[$i] . $keyword;
            } else if (ctype_alnum(str_replace('_', '', str_replace('$', '', $keyword)))) {
                if ($keyword[0] === '$') { // variable
                    if (isset($this->componentArguments[$keyword]) || $newVariables || $keyword == $this->_CompileComponentName) {
                        $newExpression .= $spaces[$i] . $keyword;
                    } else {
                        $newExpression .= $spaces[$i] . $this->_CompileExpressionPrefix . substr($keyword, 1);
                    }
                } else { // method or const or nested property
                    if ($i > 0 && $keywords[$i - 1] === '->') { // nested property or method
                        $newExpression .= $keyword;
                    } else if ($i + 1 < $count && $keywords[$i + 1] === '(') { // method call
                        // check if method exists
                        $componentName = $this->latestPageTemplate->ComponentInfo->ComponentName;
                        if ($this->componentReflectionTypes[$componentName]->hasMethod($keyword)) {
                            $newExpression .= $this->_CompileExpressionPrefix . $keyword;
                        } else {
                            // otherwise it's just a function
                            $newExpression .= $keyword;
                        }
                    } else {
                        $newExpression .= $spaces[$i] . $keyword;
                    }
                }
            } else {
                $newExpression .= $spaces[$i] . $keyword;
            }
        }
        // $this->debug($expression);
        // $this->debug($newExpression);
        // $this->debug($this->componentArguments);
        // $this->debug('----------------------');
        return $newExpression;
    }

    function compileExpression(TagItem $tagItem, array $reserved = []): string // TODO: validate expression
    {
        $expression = $tagItem->Content;
        $code = '';
        $phpCode = '';
        if ($expression[0] === '{' && $expression[strlen($expression) - 1] === '}') {
            // raw html
            $code = $this->renderReturn ? '' : '<?=';
            $phpCode = $this->convertExpressionToCode(substr($expression, 1, strlen($expression) - 2), $reserved);
            $code .= $phpCode;
            $code .= $this->renderReturn ? '' : '?>';
            $tagItem->JsExpression = $this->expressionsTranslator->convert($phpCode, true);
            $tagItem->RawHtml = true;
        } else if ($expression[0] === '#' && $expression[strlen($expression) - 1] === '#') {
            // injected html during the build
            // $this->debug($expression);
            $tagItem->ItsExpression = false;
            $phpCode = $this->convertExpressionToCode(substr($expression, 1, strlen($expression) - 2), $reserved);
            // $this->debug($phpCode);
            $_component = $this->currentComponentInstance ?? null; // use for version
            $tagItem->Content = eval('return ' . $phpCode . ';');
            $code .= $tagItem->Content;
            $tagItem->RawHtml = true;
            return $code;
        } else {
            $code = ($this->renderReturn ? '' : '<?=') . 'htmlentities(';
            $phpCode = $this->convertExpressionToCode($expression, $reserved);
            $code .= $phpCode;
            $code .= ')' . ($this->renderReturn ? '' : '?>');
            $tagItem->JsExpression = $this->expressionsTranslator->convert($phpCode, true);
            // $this->debug($phpCode . $tagItem->JsExpression);
        }
        $tagItem->PhpExpression = $phpCode;
        $detectedReferences = $this->expressionsTranslator->getVariablePaths();
        // $this->debug($detectedReferences);
        $this->requestedIncludes = array_merge(
            $this->requestedIncludes,
            $this->expressionsTranslator->getRequestedIncludes()
        );
        if (isset($detectedReferences['global'])) {
            // $this->debug($detectedReferences);
            // $this->debug($this->componentScopeVariables);
            // $this->debug($this->_CompileExpressionPrefix);
            $subscriptions = array_map(
                function ($item) {
                    $parts = explode('.', $item, 2);
                    $varPath = '';
                    if (count($parts) > 1) {
                        $varPath = '.' . $parts[1];
                    }
                    $rootVariable = $parts[0];
                    if (isset($this->componentScopeVariables[$rootVariable])) {
                        $rootVariable = $this->componentScopeVariables[$rootVariable];
                    }
                    return str_replace($this->_CompileExpressionJsPrefix, 'this.', $rootVariable . $varPath);
                },
                array_keys($detectedReferences['global']['function'])
            );
            // $this->debug(['XXX' => $tagItem->Subscriptions]);
            // $this->debug($subscriptions);
            if ($subscriptions) {
                // if ($tagItem->Subscriptions) {
                //     $this->debug($tagItem->Subscriptions);
                //     $this->debug($subscriptions);
                // }
                $tagItem->Subscriptions = $subscriptions;
                // merge with dependent subscriptions
                $componentName = $this->latestPageTemplate->ComponentInfo->ComponentName;
                // $this->debug($componentName);
                if (isset($this->componentDependencies[$componentName])) {
                    // $this->debug($tagItem->Subscriptions);
                    // $this->debug($this->componentDependencies[$componentName]);
                    $used = [];

                    $componentDeps = $this->componentDependencies[$componentName];
                    $digIn = true;
                    $i = 0;
                    while ($digIn) {
                        $digIn = false;
                        $count = count($tagItem->Subscriptions);
                        for ($i; $i < $count; $i++) {
                            // $this->debug($tagItem->Subscriptions[$i]);
                            // $this->debug($componentDeps);
                            $cleanName = str_replace('this.', '', $tagItem->Subscriptions[$i]);
                            if (isset($componentDeps[$cleanName])) {
                                foreach ($componentDeps[$cleanName] as $dep => $ghost) {
                                    if (!isset($used[$dep])) {
                                        $used[$dep] = true;
                                        $tagItem->Subscriptions[] = $dep;
                                        $digIn = true;
                                    }
                                }
                            }
                        }
                    }
                }
                // $this->debug($tagItem->Subscriptions);
            }
        }
        // $this->debug($phpCode);
        // $this->debug($tagItem->JsExpression);
        // $this->debug($tagItem->Subscriptions);
        return $code;
    }

    function resolve(ComponentInfo &$componentInfo, bool $defaultCache = false, array $params = [])
    {
        // cache service instances or parent components for slots
        // do not cache component instances
        // $this->debug($componentInfo);
        $cache = true;
        $relative = $componentInfo->Relative;
        if ($relative) {
            include_once $this->sourcePath . $componentInfo->Fullpath;
        } else {
            include_once $componentInfo->Fullpath;
        }
        if ($componentInfo->IsComponent) {
            // always new instance
            $cache = $defaultCache;
        } elseif (isset($componentInfo->IsSlot)) {
            include_once $this->buildPath . $componentInfo->BuildPath;
            return $this->resolve($this->components[$componentInfo->ComponentName], true);
        } else {
            // It's service or any class (It's not template component)
        }
        $class = $componentInfo->Namespace . '\\' . $componentInfo->Name;
        if ($cache && isset($this->Dependencies[$class])) {
            // $this->debug("From cache $class");
            return $this->Dependencies[$class];
        }
        // $this->debug("Creating $class");
        $instance = false;
        if (empty($componentInfo->Dependencies)) {
            $instance = new $class();
            if ($componentInfo->HasInit) {
                $instance->__init();
            }
        } else {
            $arguments = [];
            foreach ($componentInfo->Dependencies as $argName => $type) {
                // resolve router param
                if (isset($params[$argName])) {
                    $arguments[] = in_array($type['name'], ['int', 'float'])
                        ? (float)$params[$argName]
                        : $params[$argName];
                } else if (isset($type['default'])) {
                    $arguments[] = $type['default'];
                } else if (isset($type['null'])) {
                    $arguments[] = null;
                } else if (isset($type['builtIn'])) {
                    switch ($type['name']) { // TODO: more types
                        case 'string': {
                                $arguments[] = '';
                                break;
                            }
                        case 'int': {
                                $arguments[] = 0;
                                break;
                            }
                        default: {
                                throw new Exception("Type '{$type['name']}' is not configured.");
                                break;
                            }
                    }
                } else {
                    $arguments[] = $this->resolve($this->components[$type['name']]);
                }
            }
            if ($componentInfo->HasInit) {
                $instance = new $class();
                $instance->__init(...$arguments);
            } else {
                $instance = new $class(...$arguments);
            }
        }
        // always cache for slots
        $this->Dependencies[$class] = $instance;
        return $instance;
    }

    function renderDynamicTag($tagName, $slotName, ...$args)
    {
        $tagInfo = &$this->components[$slotName];
        include_once $this->sourcePath . $tagInfo->Fullpath;
        include_once $this->buildPath . $tagInfo->BuildPath;
        return ($tagInfo->RenderFunction)(...$args);
    }

    function renderComponent(
        ?string $componentName,
        array $params,
        ?BaseComponent $parentComponent,
        array $slots,
        array $componentArguments,
        ...$slotArguments
    ) {
        //$this->debug($this->templates[$componentName]->ComponentInfo);
        if ($componentName) {
            $componentInfo = &$this->components[$componentName];
            // $this->debug($componentInfo);
            // $this->debug('============' . $componentName);
            $middlewareBreak = false;
            if ($componentInfo->IsComponent) {
                $fullClassName = $componentInfo->Namespace . '\\' . $componentInfo->Name;
                // TODO: allow middleware only for root (first) render, could be redirect
                if (isset($fullClassName::$_beforeStart)) {
                    /** @var string[] $actions*/
                    $middlewareActions = $fullClassName::$_beforeStart;
                    foreach ($middlewareActions as $beforeAction) {
                        $beforeActionComponent = strpos($beforeAction, '\\') !== false ?
                            substr(strrchr($beforeAction, "\\"), 1)
                            : $beforeAction;
                        $beforeActionInfo = &$this->components[$beforeActionComponent];
                        /** @var IMiddleware $beforeActionInstance*/
                        $beforeActionInstance = $this->resolve($beforeActionInfo, false);
                        $middlewareBreak = true;
                        $beforeActionInstance->run(function () use (&$middlewareBreak) {
                            $middlewareBreak = false;
                        });
                        if ($middlewareBreak) {
                            break;
                        }
                    }
                }
            }
            if ($middlewareBreak) {
                return '';
            }
            // if(!$componentInfo){
            //     $this->debug($this->components);
            // }
            $classInstance = $this->resolve($componentInfo, false, $params);
            // if ($parentComponent !== null) {
            //$parentClassName = get_class($parentComponent);
            $slotsQueue = $slots;
            $slotsBefore = [];
            if (isset($this->_slots[$componentInfo->ComponentName])) {
                $slotsQueue = $slotsQueue + $this->_slots[$componentInfo->ComponentName];
                $slotsBefore = $this->_slots[$componentInfo->ComponentName];
            }
            $this->_slots[$componentInfo->ComponentName] = $slotsQueue;
            // print_r($componentInfo->ComponentName . ' == ' . $componentName);
            // print_r($this->_slots);
            // }
            // TODO: reuse instance, TODO: dependency inject
            // init input properties
            // TODO: cache properties
            $componentInfo->IsComponent && $componentInfo->HasBeforeMount && $classInstance->__beforeMount();
            foreach ($componentArguments as $key => $inputValue) {
                if (isset($componentInfo->Inputs[$key])) {
                    $classInstance->{$key} = $inputValue;
                }
            }
            $componentInfo->IsComponent && $componentInfo->HasMounted && $classInstance->__mounted();
            $renderFunction = $this->components[$componentName]->RenderFunction ?? '';
            if ($componentInfo->IsComponent) {
                // always new instance
                if ($componentInfo->HasVersions) {
                    $version = $classInstance->__version();
                    include_once $this->buildPath . $componentInfo->Versions[$version]['BuildPath'];
                    $renderFunction = $componentInfo->Versions[$version]['RenderFunction'];
                } else {
                    include_once $this->buildPath . $componentInfo->BuildPath;
                }
            }
            // $this->debug(func_get_args());
            $content = $renderFunction($classInstance, $this, $slotsQueue, ...$slotArguments);
            $this->_slots[$componentInfo->ComponentName] = $slotsBefore;
            return $content;
        }
    }

    function compileComponentExpression(TagItem $tagItem, string &$html, ?string $slotName = null, array $inputArguments = []): void
    {
        // generate slot(s)
        $lastLineIsSpace = false; // $this->lastLineIsSpace;
        $children = $tagItem->getChildren();
        $slots = [];
        $slotContentNameExpr = false;
        $slotContentName = '';
        $componentBaseName = '';
        if (!empty($children)) { // has slot(s)

            if ($tagItem->Content === 'slotContent') { // <slotContent name=""
                $defaultTagItem = new TagItem();
                foreach ($children as &$childTag) {
                    if (
                        $childTag->Type->Name !== TagItemType::Attribute
                        && $childTag->Type->Name !== TagItemType::AttributeValue
                    ) { // default content
                        $defaultTagItem->addChild($childTag);
                    } else if ($childTag->Type->Name === TagItemType::Attribute) {
                        if ($childTag->Content === 'name') {
                            $slotNameAttributeValues = $childTag->getChildren();
                            if (!empty($slotNameAttributeValues)) {
                                $slotContentName = $slotNameAttributeValues[0]->Content;
                                $slotContentNameExpr = "'{$slotNameAttributeValues[0]->Content}'";
                                if ($slotNameAttributeValues[0]->ItsExpression) {
                                    $slotContentNameExpr = $this->convertExpressionToCode($slotNameAttributeValues[0]->Content);
                                    $slotContentName = '';
                                }
                            }
                        }
                    }
                }
                $tagItem = $defaultTagItem;
            }

            $partialComponentName = $slotContentName ? '_' . ucfirst($slotContentName) : '_Slot';
            $slotFileName = "{$this->latestPageTemplate->ComponentInfo->ComponentName}" .
                "$partialComponentName";
            if (!isset($this->slotCounterMap[$slotFileName])) {
                $this->slotCounterMap[$slotFileName] = 0;
            } else {
                $this->slotCounterMap[$slotFileName]++;
                $slotFileName .= $this->slotCounterMap[$slotFileName];
            }

            $componentBaseName = $slotFileName;
            $slotPageTemplate = new PageTemplate();
            $slotPageTemplate->ItsSlot = true;
            $slotPageTemplate->RootTag = $tagItem;
            $slotPageTemplate->ComponentInfo = new ComponentInfo();
            $slotPageTemplate->ComponentInfo->IsComponent = false;
            $slotPageTemplate->ComponentInfo->IsSlot = true;
            $slotPageTemplate->ComponentInfo->Name = $componentBaseName;
            $slotPageTemplate->ComponentInfo->ComponentName = $this->latestPageTemplate->ComponentInfo->ComponentName;
            $slotPageTemplate->ComponentInfo->Namespace = $this->latestPageTemplate->ComponentInfo->Namespace;
            $slotPageTemplate->ComponentInfo->Tag = $componentBaseName;
            //$this->debug($this->latestPageTemplate->ComponentInfo);
            $pathinfo = pathinfo($this->latestPageTemplate->ComponentInfo->Fullpath);
            $pathWOext = $pathinfo['dirname'] . DIRECTORY_SEPARATOR . $componentBaseName;
            $phpPath = $pathWOext . '.php';
            $htmlPath = $pathWOext . '.html';
            $slotPageTemplate->ComponentInfo->TemplatePath = $this->getRelativeSourcePath($htmlPath);
            $slotPageTemplate->ComponentInfo->Fullpath = $this->latestPageTemplate->ComponentInfo->Fullpath;
            $slotPageTemplate->ComponentInfo->Relative = $this->latestPageTemplate->ComponentInfo->Relative;
            $slotPageTemplate->Path = $htmlPath;
            $this->templates[$componentBaseName] = $slotPageTemplate;
            $this->components[$componentBaseName] = $slotPageTemplate->ComponentInfo;
            $this->build($this->templates[$componentBaseName]);
            $this->save($this->templates[$componentBaseName]);
        }
        //render component
        $componentName = $tagItem->ItsExpression
            ? $this->convertExpressionToCode($tagItem->Content)
            : "'{$tagItem->Content}'";
        if ($slotName) {
            $componentName = "$slotName ? $slotName : '{$componentBaseName}'";
        }
        $eol = PHP_EOL;
        $codeBegin = $this->renderReturn ? $eol : ($lastLineIsSpace ? $eol : '') . "<?php$eol";
        $codeMiddle = $this->renderReturn ? "\$_content .= " : '';
        $codeEnd = $this->renderReturn ? '' : '?>';

        if ($slotContentNameExpr) {

            $html .= "{$codeBegin}{$this->indentation}\$slotContents[$slotContentNameExpr] = '{$componentBaseName}';$eol{$codeEnd}";
        } else {
            $scopeArguments = implode(', ', $this->componentArguments);
            if ($scopeArguments) {
                $scopeArguments = ', ' . $scopeArguments . ', ...$scope';
            } else {
                $scopeArguments = ', ...$scope';
            }
            $inputArgumentsCode = '[]';
            if (!empty($inputArguments)) {
                $inputArgumentsCode = '[' . PHP_EOL;
                foreach ($inputArguments as $key => $expressionCode) {
                    $inputArgumentsCode .= "'$key' => $expressionCode," . PHP_EOL;
                }
                $inputArgumentsCode .= ']';
            }
            $slotsExpression = $componentBaseName ? "'$componentBaseName'" : 'false';
            // dynamic tag
            // $dynamicTagCode = '';
            // if ($componentName[0] === '$') {
            //     $dynamicTagCode = "\$pageEngine->isTag($componentName)" .
            //         "{$eol}{$this->identation}? \$pageEngine->RenderDynamicTag($componentName," .
            //         " $slotsExpression, \$_component, \$pageEngine, \$slots, ...\$scope)" .
            //         "{$eol}{$this->identation}: ";
            // }

            $html .= $codeBegin .
                $this->indentation . "\$slotContents[0] = $slotsExpression;" .
                PHP_EOL . $this->indentation . "{$codeMiddle}\$pageEngine->renderComponent(" .
                "$componentName, " .
                "[], " .
                "{$this->_CompileComponentName}, " .
                "\$slotContents, " .
                "$inputArgumentsCode" .
                "$scopeArguments);" .
                PHP_EOL . $this->indentation . "\$slotContents = [];" .
                $codeEnd;
        }
    }

    function compileSlotExpression(TagItem $tagItem, string &$html): void
    {
        $slotName = false;
        $defaultContent = false;
        $componentName = '$slots[0]';
        $children = $tagItem->getChildren();
        if (!empty($children)) {
            $defaultTagItem = new TagItem();
            foreach ($children as &$childTag) {
                if (
                    $childTag->Type->Name !== TagItemType::Attribute
                    && $childTag->Type->Name !== TagItemType::AttributeValue
                ) { // default content
                    $defaultTagItem->addChild($childTag);
                    $defaultContent = true;
                } else if ($childTag->Type->Name === TagItemType::Attribute) {
                    if ($childTag->Content === 'name') {
                        $slotNameAttributeValues = $childTag->getChildren();
                        if (!empty($slotNameAttributeValues)) {
                            $slotName = "'{$slotNameAttributeValues[0]->Content}'";
                            if ($slotNameAttributeValues[0]->ItsExpression) {
                                $slotName = $this->convertExpressionToCode($slotNameAttributeValues[0]->Content);
                            }
                            $componentName = "\$slots[$slotName] ?? null";
                        }
                    }
                }
            }
            if ($defaultContent) {
                $this->compileComponentExpression($defaultTagItem, $html, $componentName);
            }
        }
        if (!$defaultContent) {
            $codeBegin = $this->renderReturn ? PHP_EOL . $this->indentation . "\$_content .=" : "<?php";
            $codeEnd = $this->renderReturn ? '' : '?>';
            $html .= "$codeBegin \$pageEngine->renderComponent($componentName, [], {$this->_CompileComponentName}, \$slotContents, [], ...\$scope); $codeEnd";
        }
    }

    function flushBuffer(string &$html, string &$codeToAppend)
    {
        if ($codeToAppend !== '') {
            if ($this->renderReturn) {
                $html .= PHP_EOL . $this->indentation . "\$_content .= " .
                    var_export($codeToAppend, true) . ";";
            } else {
                $html .= $codeToAppend;
            }
            $codeToAppend = '';
        }
    }

    function startForeach(string $foreach, string &$html, string &$codeToAppend, array &$foreachArguments)
    {
        $this->flushBuffer($html, $codeToAppend);
        $foreachParts = explode(' as ', $foreach, 2);
        $foreachSource = $this->convertExpressionToCode($foreachParts[0]);
        $foreachAsParts = explode('=>', $foreachParts[1]);
        //$this->debug($tagItem);
        //$this->debug($foreach);
        //$this->debug($foreachAsParts);
        foreach ($foreachAsParts as $foreachArgument) {
            $argument = trim($foreachArgument);
            $foreachArguments[$argument] = $argument;
        }
        $html .= ($this->renderReturn ? '' : "<?php") . PHP_EOL . $this->indentation .
            "foreach($foreachSource as {$foreachParts[1]}){" .
            PHP_EOL . $this->indentation . ($this->renderReturn ? '' : "?>");
    }

    function endForeach($foreach, ?DataExpression $foreachExpression, $foreachArguments, &$html, string &$codeToAppend)
    {
        if ($foreach) {
            $this->flushBuffer($html, $codeToAppend);
            $html .= ($this->renderReturn ? '' : "<?php") . PHP_EOL . $this->indentation .
                "}" .
                PHP_EOL . $this->indentation . ($this->renderReturn ? '' : "?>");
            foreach ($foreachArguments as $argument) {
                unset($this->componentArguments[$argument]);
            }
            unset($this->componentScopeVariables[$foreachExpression->ForItem]);
            // $this->debug($foreach);
            // $this->debug($foreachArguments);
            // $this->debug($this->componentArguments);
            $this->extraLine = true;
        }
    }

    function startIf(string $ifExpression, string &$html, string &$codeToAppend)
    {
        $this->flushBuffer($html, $codeToAppend);
        $ifCode = $this->convertExpressionToCode($ifExpression);
        $html .= ($this->renderReturn ? '' : "<?php") . PHP_EOL . $this->indentation .
            "if($ifCode){" .
            PHP_EOL . $this->indentation . ($this->renderReturn ? '' : "?>");
    }

    function closeIf(string $ifExpression, string &$html, string &$codeToAppend, bool $closeIfTag)
    {
        if ($ifExpression) {
            $this->flushBuffer($html, $codeToAppend);
            $html .= ($this->renderReturn ? '' : "<?php") . PHP_EOL . $this->indentation .
                "}" .
                ($closeIfTag ? (PHP_EOL . $this->indentation . ($this->renderReturn ? '' : "?>")) : '');
            $this->extraLine = true;
        }
    }

    function startElseIf(string $elseIfExpression, string &$html, string &$codeToAppend)
    {
        $this->flushBuffer($html, $codeToAppend);
        $ifCode = $this->convertExpressionToCode($elseIfExpression);
        $html .= " else if ($ifCode){" .
            PHP_EOL . $this->indentation . ($this->renderReturn ? '' : "?>");
    }

    function closeElseIf(string $elseIfExpression, string &$html, string &$codeToAppend, bool $closeIfTag)
    {
        if ($elseIfExpression) {
            $this->flushBuffer($html, $codeToAppend);
            $html .= ($this->renderReturn ? '' : "<?php") . PHP_EOL . $this->indentation .
                "}" .
                ($closeIfTag ? PHP_EOL . $this->indentation . ($this->renderReturn ? '' : "?>") : '');
            $this->extraLine = true;
        }
    }

    function startElse(string &$html, string &$codeToAppend)
    {
        $this->flushBuffer($html, $codeToAppend);
        $html .= " else {" .
            PHP_EOL . $this->indentation . ($this->renderReturn ? '' : "?>");
    }

    function closeElse(string $elseExpression, string &$html, string &$codeToAppend)
    {
        if ($elseExpression) {
            $this->flushBuffer($html, $codeToAppend);
            $html .= ($this->renderReturn ? '' : "<?php") . PHP_EOL . $this->indentation .
                "}" .
                PHP_EOL . $this->indentation . ($this->renderReturn ? '' : "?>");
            $this->extraLine = true;
        }
    }

    function getCloseIfTag(TagItem &$tagItem): bool
    {
        $closeIfTag = true;
        $parentChilds = $tagItem->parent()->getChildren();
        $startLookingForElif = false;
        $textsToSkip = [];
        foreach ($parentChilds as $key => &$parentChildren) {
            if ($parentChildren === $tagItem) {
                $startLookingForElif = true;
                continue;
            }
            if ($startLookingForElif) {
                if (
                    $parentChildren->Type->Name == TagItemType::Tag
                    || $parentChildren->Type->Name == TagItemType::Component
                ) {
                    $elifChildren = $parentChildren->getChildren();
                    foreach ($elifChildren as &$elifChild) {
                        if (
                            $elifChild->Type->Name == TagItemType::Attribute
                            && ($elifChild->Content === 'else-if' || $elifChild->Content === 'else')
                        ) {
                            $closeIfTag = false;
                            foreach ($textsToSkip as $index => &$textItem) {
                                $textItem->Skip = true;
                                unset($parentChilds[$index]);
                            }
                        }
                    }
                    break;
                } elseif ($parentChildren->Type->Name === TagItemType::TextContent) {
                    $textsToSkip[$key] = &$parentChildren;
                }
            }
        }
        // $tagItem->parent()->setChildren($parentChilds);
        return $closeIfTag;
    }

    function getChildValues(TagItem &$tagItem): string
    {
        $combinedValue = '';
        $children = $tagItem->getChildren();
        foreach ($children as &$child) {
            $combinedValue .= $child->Content;
        }
        return $combinedValue;
    }

    function getNextIterationKey(): string
    {
        ++$this->forIterationKey;
        return "_key{$this->forIterationKey}";
    }

    function combineChildren(
        TagItem $childTag,
        bool $expression = true,
        array $reserved = [],
        bool $foreach = false,
        bool $concat = false,
        bool $mergeChildren = true
    ): string {
        $attrValues = $childTag->getChildren();
        $count = count($attrValues);
        $newValueContent = '';
        $glue = '';
        foreach ($attrValues as $attrValue) {
            $newValueContent .= $glue .
                (!$concat || $attrValue->ItsExpression
                    ? $attrValue->Content
                    : var_export($attrValue->Content, true));
            if ($concat) {
                $glue = ' . ';
            }
        }
        // replace children with one expression
        if ($mergeChildren) {
            $newChild = $attrValues[0];
            $newChild->Content = $newValueContent;
            $newChild->ItsExpression = $concat || $newChild->ItsExpression;
            $childTag->setChildren([$newChild]);

            if ($expression) {
                $newChild->ItsExpression = true;
                $this->compileExpression($newChild, $reserved);
                if ($foreach) {
                    $newChild->DataExpression = new DataExpression();
                    $foreachParts = explode(' as ', $newChild->JsExpression, 2);
                    $newChild->DataExpression->ForData = $foreachParts[0];
                    $foreachAsParts = explode('=>', $foreachParts[1]);
                    $newChild->DataExpression->ForItem = $foreachAsParts[0];
                    if (count($foreachAsParts) > 1) {
                        $newChild->DataExpression->ForKey = $foreachAsParts[0];
                        $newChild->DataExpression->ForItem = $foreachAsParts[1];
                    } else {
                        $newChild->DataExpression->ForKey = $this->getNextIterationKey();
                    }
                }
            }
        }
        return $newValueContent;
    }

    function buildTag(TagItem &$tagItem, string &$html, string &$codeToAppend): void
    {
        $foreach = false;
        $foreachExpression = null;
        $ifExpression = false;
        $closeIfTag = true;
        $elseIfExpression = false;
        $elseExpression = false;
        $firstFound = false;
        $breakAll = false;
        /** @var TagItem[] */
        $children = $tagItem->getChildren();
        if (
            $tagItem->Type->Name == TagItemType::Tag
            || $tagItem->Type->Name == TagItemType::Component
        ) {
            foreach ($children as &$childTag) {
                if (
                    $childTag->Type->Name == TagItemType::Attribute
                    && $childTag->Content === 'foreach'
                ) { //foreach detected
                    $childTag->Skip = true;
                    $foreach = $this->combineChildren($childTag, true, [], true);
                    if (!$firstFound) {
                        $firstFound = 'foreach';
                    }
                    $foreachParts = explode(' as ', $foreach, 2);
                    $dataExpression = $childTag->getChildren()[0]->DataExpression;
                    $foreachExpression = $dataExpression;
                    $forDataExpression = $dataExpression->ForData;
                    $forDataExpressionParts = explode('.', $forDataExpression, 2);
                    $forDataExpressionRoot = $forDataExpressionParts[0];
                    if (isset($this->componentScopeVariables[$forDataExpressionRoot])) {
                        $forDataExpressionRoot = $this->componentScopeVariables[$forDataExpressionRoot];
                    }
                    $this->componentScopeVariables[$dataExpression->ForItem] = $forDataExpressionRoot . '.' . $forDataExpressionParts[1] . '[key]';
                    // $this->debug($dataExpression);
                    $foreachAsParts = explode('=>', $foreachParts[1]);
                    foreach ($foreachAsParts as $foreachArgument) {
                        $argument = trim($foreachArgument);
                        $this->componentArguments[$argument] = $argument;
                    }
                    continue;
                }

                if (
                    $childTag->Type->Name == TagItemType::Attribute
                    && $childTag->Content === 'if'
                ) { // if detected
                    $childTag->Skip = true;
                    $ifExpression = $this->combineChildren($childTag);
                    if (!$firstFound) {
                        $firstFound = 'if';
                    }
                    // detect if else of else-if towards
                    $closeIfTag = $this->getCloseIfTag($tagItem);
                    continue;
                }
                if (
                    $childTag->Type->Name == TagItemType::Attribute
                    && $childTag->Content === 'else-if'
                ) { // else if detected
                    $childTag->Skip = true;
                    $elseIfExpression = $this->combineChildren($childTag);
                    // detect if else of else-if towards
                    $closeIfTag = $this->getCloseIfTag($tagItem);
                    continue;
                }
                if (
                    $childTag->Type->Name == TagItemType::Attribute
                    && $childTag->Content === 'else'
                ) { // else detected
                    $childTag->Skip = true;
                    $elseExpression = true;
                    continue;
                }
            }
        }

        $foreachArguments = [];
        if ($elseExpression) {
            $this->startElse($html, $codeToAppend);
        }
        if ($elseIfExpression) {
            $this->startElseIf($elseIfExpression, $html, $codeToAppend);
        }
        if ($ifExpression && $firstFound === 'if') {
            $this->startIf($ifExpression, $html, $codeToAppend);
        }
        if ($foreach) {
            $this->startForeach($foreach, $html, $codeToAppend, $foreachArguments);
        }
        if ($ifExpression && $firstFound !== 'if') {
            $this->startIf($ifExpression, $html, $codeToAppend);
        }

        $dynamicTagDetected = $tagItem->Type->Name == TagItemType::Tag && $tagItem->ItsExpression;

        if ($tagItem->Type->Name == TagItemType::Component) {
            $breakAll = true;
        }
        // $codeToAppend = '';
        if ($tagItem->Type->Name == TagItemType::Tag && !$tagItem->ItsExpression) {
            if ($tagItem->Content === 'slot') { // render slot
                // $this->debug($tagItem);
                // foreach($tagItem->parent()->getChildren() as &$chld){
                //     var_dump($chld->Content);
                // }
                // lines formatting
                // if (
                //     $this->previousItem->Type->Name === TagItemType::TextContent
                //     && $this->previousItem->Content !== null
                // ) {
                //     $breakChar = PHP_EOL;
                //     $lines = explode(PHP_EOL, $this->previousItem->Content);
                //     $linesCount = count($lines);
                //     if ($linesCount > 1) {
                //         $lastLine = $lines[$linesCount - 1];
                //         if (ctype_space($lastLine)) {
                //             $html = substr($html, 0, -strlen($lastLine));
                //             // $html .= $breakChar;
                //             // TODO: remove last new line, add this one
                //         } elseif ($lastLine == '') {
                //             $html .= $breakChar;
                //         }
                //     }
                // }
                if ($codeToAppend) {
                    if ($this->renderReturn) {
                        $html .= PHP_EOL . $this->indentation . "\$_content .= " .
                            var_export($codeToAppend, true) . ";";
                    } else {
                        $html .= $codeToAppend;
                    }
                    $codeToAppend = '';
                }
                $this->compileSlotExpression($tagItem, $html);
                $this->extraLine = true;
                $breakAll = true;
            }
            if ($tagItem->Content === 'slotContent') { // render named slot (Component with named slots)
                $breakAll = true;
            }
            //$html .= "<$replaceByTag data-component=\"{$content}\"";
        }
        if ($tagItem->Skip) {
            $this->previousItem = $tagItem;
            $breakAll = true;
        }
        if (!$breakAll) {
            $noChildren = empty($children);
            $noContent = true;
            $selfClosing = false;
            $content = $tagItem->ItsExpression
                ? $this->compileExpression($tagItem)
                : $tagItem->Content;
            $skipTagRender = false;
            if ($tagItem->Type->Name == TagItemType::Tag) {
                $skipTagRender = $tagItem->Content === 'template';
                if (!$skipTagRender) {
                    if ($dynamicTagDetected) {
                        // put if
                        if ($this->renderReturn) {
                            $html .= PHP_EOL . $this->indentation . "\$_content .= " .
                                var_export($codeToAppend, true) . ";";
                            $codeToAppend = '';
                            $html .= PHP_EOL . $this->indentation .
                                "if(\$pageEngine->isTag($content)) {";
                        } else {
                            $codeToAppend .= '<?php' . PHP_EOL . $this->indentation .
                                "if(\$pageEngine->isTag({$tagItem->PhpExpression})) {" . PHP_EOL . '?>';
                        }
                    }
                    $codeToAppend .= '<';
                    if ($this->renderReturn) {
                        if ($tagItem->ItsExpression) {
                            $html .= PHP_EOL . $this->indentation . "\$_content .= " .
                                var_export($codeToAppend, true) . ";";
                            $html .= PHP_EOL . $this->indentation . "\$_content .= " .
                                $content . ";";
                            $codeToAppend = '';
                        } else {
                            $codeToAppend .= $content;
                        }
                    } else {
                        $codeToAppend .= $content;
                    }
                    if (isset($this->selfClosingTags[strtolower($content)])) {
                        $selfClosing = true;
                    }
                }
                if (!$noChildren) { // merge attributes                    
                    $newChildren = [];
                    foreach ($children as &$childTag) {
                        if ($childTag->Type->Name === TagItemType::Attribute) {
                            if ($skipTagRender && !$childTag->Skip) { // template can't have attributes
                                trigger_error("`template` tag can't have attributes: attribute '{$childTag->Content}'", E_USER_WARNING);
                                continue;
                            }
                            $attributeName = $childTag->Content;
                            $mergeValues = $childTag->getChildren();
                            $valueToReplace = false;
                            if ($attributeName[0] === '(' || $attributeName === 'model' || $childTag->ItsExpression) { // event
                                $childTag->Skip = !$childTag->ItsExpression;
                                $attrValues = $childTag->getChildren();
                                $newValueContent = '';
                                foreach ($attrValues as $attrValue) {
                                    $newValueContent .= $attrValue->Content;
                                }

                                if ($childTag->ItsExpression) {
                                    $dynamicEventTag = new TagItem();
                                    $dynamicEventTag->ItsExpression = true;
                                    $dynamicEventTag->Content = $newValueContent;
                                    $this->compileExpression($dynamicEventTag, ['$event' => true]);
                                    $childTag->DynamicChild = $dynamicEventTag;
                                } else {
                                    // replace children with one expression
                                    $newChild = $attrValues[0];
                                    $newChild->Content = $newValueContent;
                                    $childTag->setChildren([$newChild]);
                                    $newChild->ItsExpression = true;
                                    $this->compileExpression($newChild, ['$event' => true]);
                                }
                            }
                            $originContent = $attributeName;
                            if (strpos($attributeName, '.') !== false) {
                                $childTag->OriginContent = $attributeName;
                                $parts = explode('.', $attributeName, 2);
                                $attributeName = $parts[0];
                                $valueToReplace = $parts[1];
                                $childTag->Content = $attributeName;
                                $this->combineChildren($childTag);
                            }
                            if (isset($newChildren[$attributeName])) { // merge values
                                $firstTime = true;
                                if ($newChildren[$attributeName]->OriginContents == null) {
                                    $newChildren[$attributeName]->OriginContents = [];
                                }
                                foreach ($mergeValues as &$attrValueItem) {

                                    if ($valueToReplace !== false) {
                                        $attrValueItem->Content = "{$attrValueItem->Content} ? ' $valueToReplace' : ''";
                                        $newChildren[$attributeName]->addChild($attrValueItem);
                                        $newChildren[$attributeName]->OriginContents[] = $originContent;
                                        // $this->debug($attrValueItem->Content);
                                        // $this->debug($attrValueItem->ItsExpression);
                                        break;
                                    } else {
                                        if ($firstTime) {
                                            $spaceValue = new TagItem();
                                            $spaceValue->Type = new TagItemType(TagItemType::AttributeValue);
                                            $spaceValue->Content = ' ';
                                            $newChildren[$attributeName]->addChild($spaceValue);
                                            $firstTime = false;
                                        }
                                        $newChildren[$attributeName]->addChild($attrValueItem);
                                        $newChildren[$attributeName]->OriginContents[] = $originContent;
                                    }
                                }
                            } else {
                                if ($valueToReplace !== false) {
                                    $mergeValues[0]->Content = "{$mergeValues[0]->Content} ? '$valueToReplace' : ''";
                                }
                                $newChildren[$attributeName] = $childTag;
                                if ($newChildren[$attributeName]->OriginContents == null) {
                                    $newChildren[$attributeName]->OriginContents = [];
                                }
                                $newChildren[$attributeName]->OriginContents[] = $originContent;
                            }
                        } else {
                            $newChildren[] = $childTag;
                        }
                    }
                    //$this->debug($children);
                    $tagItem->setChildren(array_values($newChildren));
                    $children = $tagItem->getChildren();
                    //$this->debug($children);
                }
            }

            if (
                $this->previousItem->Type->Name === TagItemType::Comment
                && $tagItem->Type->Name !== TagItemType::Comment
            ) {
                $codeToAppend .= '-->';
            }

            if (
                $tagItem->Type->Name == TagItemType::TextContent
                || $tagItem->Type->Name == TagItemType::Comment
            ) {
                if ($this->extraLine && !$this->renderReturn) {
                    $this->extraLine = false;
                    if ($tagItem->Content[0] === "\n" || $tagItem->Content[0] === "\r") {
                        $codeToAppend .= PHP_EOL;
                    }
                }
                if (
                    $tagItem->Type->Name === TagItemType::Comment
                    && $this->previousItem->Type->Name !== TagItemType::Comment
                ) {
                    $codeToAppend .= '<!--';
                }
                if ($this->renderReturn && $tagItem->ItsExpression) {
                    $html .= PHP_EOL . $this->indentation . "\$_content .= " .
                        var_export($codeToAppend, true) . ";";
                    $html .= PHP_EOL . $this->indentation . "\$_content .= " .
                        $content . ";";
                    $codeToAppend = '';
                } else {
                    $codeToAppend .= $content;
                }
                $this->extraLine = $tagItem->ItsExpression;
                $this->lastLineIsSpace = false;
                if (!$tagItem->ItsExpression) {
                    $lines = explode(PHP_EOL, $content);
                    if ($lines[count($lines) - 1] === '' || ctype_space($lines[count($lines) - 1])) {
                        $this->lastLineIsSpace = true;
                    }
                }
            } else {
                $this->lastLineIsSpace = false;
                $this->extraLine = false;
            }

            if ($tagItem->Type->Name === TagItemType::Attribute) {
                if (
                    !$noChildren && count($children) == 1 && $children[0]->ItsExpression
                    && isset($this->booleanAttributes[strtolower($tagItem->Content)])
                ) { // attribute is boolean, TODO: check argument expression to have boolean type
                    // compile if based on expression
                    $condition = $this->convertExpressionToCode($children[0]->Content);
                    if ($this->renderReturn) {
                        $this->flushBuffer($html, $codeToAppend);
                        $html .= PHP_EOL . $this->indentation . "\$_content .= " .
                            "$condition ? ' {$tagItem->Content}=\"{$tagItem->Content}\"' : ''" . ";";
                    } else {
                        $html .= $codeToAppend;
                        $codeToAppend = '';
                        $html .= "<?=$condition ? ' {$tagItem->Content}=\"{$tagItem->Content}\"' : ''?>";
                        $this->previousItem = $tagItem;
                    }
                    $this->compileExpression($children[0]);
                    return;
                }
                $codeToAppend .= ' ';
                if ($tagItem->ItsExpression) {
                    if ($this->renderReturn) {
                        $html .= PHP_EOL . $this->indentation . "\$_content .= " .
                            var_export($codeToAppend, true) . ";";
                        $html .=  PHP_EOL . $this->indentation . "if ({$tagItem->PhpExpression}[0] !== '(') {";
                        // TODO: fix identation
                        $html .= PHP_EOL . $this->indentation . "\$_content .= " .
                            $content . ";";
                        $codeToAppend = '';
                    } else {
                        $codeToAppend .= '<?php' . PHP_EOL . $this->indentation .
                            "if ({$tagItem->PhpExpression}[0] !== '(') {" . PHP_EOL . '?>';
                        $codeToAppend .= $content;
                    }
                } else {
                    $codeToAppend .= $content;
                }

                $codeToAppend .=  ($noChildren
                    ? ''
                    : '="');
            }

            if ($tagItem->Type->Name === TagItemType::AttributeValue) {
                if ($tagItem->ItsExpression && $this->renderReturn) {
                    $html .= PHP_EOL . $this->indentation . "\$_content .= " .
                        var_export($codeToAppend, true) . ";";
                    $html .= PHP_EOL . $this->indentation . "\$_content .= " .
                        $content . ";";
                    $codeToAppend = '';
                } else {
                    $codeToAppend .= $tagItem->ItsExpression ? $content : htmlentities($content);
                }
            }
            // CHILDREN scope
            if (!$noChildren) {
                foreach ($children as &$childTag) {
                    if (
                        $childTag->Type->Name === TagItemType::TextContent
                        || $childTag->Type->Name === TagItemType::Tag
                        || $childTag->Type->Name === TagItemType::Component
                    ) {
                        if ($noContent) {
                            $noContent = false;
                            if (!$selfClosing && !$skipTagRender) {
                                $codeToAppend .= '>';
                            }
                        }
                    }
                    if ($dynamicTagDetected) {
                        $childTag->Skip = false;
                        // $this->debug($childTag);
                    }
                    // if ($codeToAppend) {
                    //     if ($this->renderReturn) {
                    //         $html .= PHP_EOL . $this->identation . "\$_content .= " .
                    //             var_export($codeToAppend, true) . ";";
                    //     } else {
                    //         $html .= $codeToAppend;
                    //     }
                    //     $codeToAppend = '';
                    // }
                    $this->buildTag($childTag, $html, $codeToAppend);
                }
            }
            // END CHILDREN scope
            if ($tagItem->Type->Name === TagItemType::Attribute) {

                $codeToAppend .= ($noChildren ? '' : '"');
                if ($tagItem->ItsExpression) {
                    if ($this->renderReturn) {
                        $html .= PHP_EOL . $this->indentation . "\$_content .= " .
                            var_export($codeToAppend, true) . ";";
                        $html .=  PHP_EOL . $this->indentation . '}';
                        $codeToAppend = '';
                    } else {
                        $codeToAppend .= '<?php' . PHP_EOL . $this->indentation .
                            '}' . PHP_EOL . '?>';
                    }
                }
            }

            if ($tagItem->Type->Name === TagItemType::Tag) {
                if (!$skipTagRender) {
                    if ($selfClosing) {
                        $codeToAppend .= '/>';
                    } else {
                        if ($noContent) {
                            $codeToAppend .= '>';
                        }

                        if ($this->renderReturn) {
                            if ($tagItem->ItsExpression) {
                                $codeToAppend .= '</';
                                $html .= PHP_EOL . $this->indentation . "\$_content .= " .
                                    var_export($codeToAppend, true) . ";";
                                $html .= PHP_EOL . $this->indentation . "\$_content .= " .
                                    $content . ";";
                                $codeToAppend = '>';
                            } else {
                                $codeToAppend .= '</' . $content . '>';
                            }
                        } else {
                            $codeToAppend .= '</' . $content . '>';
                        }
                        $this->extraLine = false;
                    }
                    if ($dynamicTagDetected) {
                        // put if
                        if ($this->renderReturn) {
                            $html .= PHP_EOL . $this->indentation . "\$_content .= " .
                                var_export($codeToAppend, true) . ";";
                            $codeToAppend = '';
                            $html .= PHP_EOL . $this->indentation . "} else {";
                        } else {
                            $codeToAppend .= '<?php' . PHP_EOL . $this->indentation .
                                '} else {' . PHP_EOL . '?>';
                        }
                    }
                }
            }
        }
        // ======================================================================
        if (
            $tagItem->Type->Name == TagItemType::Component
            || ($tagItem->Type->Name == TagItemType::Tag && $tagItem->ItsExpression)
        ) {

            $inputArguments = [];
            // extract slotContents and input arguments
            $children = $tagItem->getChildren();
            // $this->debug($tagItem->Content  .count($children));            
            foreach ($children as &$childTag) {
                if (
                    $childTag->Type->Name === TagItemType::Tag
                    && $childTag->Content === 'slotContent'
                ) { // slot content
                    if ($codeToAppend) {
                        if ($this->renderReturn) {
                            $html .= PHP_EOL . $this->indentation . "\$_content .= " .
                                var_export($codeToAppend, true) . ";";
                        } else {
                            $html .= $codeToAppend;
                        }
                        $codeToAppend = '';
                    }
                    $this->compileComponentExpression($childTag, $html);
                } else if ($childTag->Type->Name === TagItemType::Attribute && !$childTag->Skip) {
                    $childTag->Skip = true; // component can't have attributes
                    // pass arguments
                    // compile props
                    $values = $childTag->getChildren();
                    foreach ($values as $propValue) {
                        $this->compileExpression($propValue);
                    }
                    // $this->debug($tagItem->Content);                    
                    if ($dynamicTagDetected || isset($this->components[$tagItem->Content])) {
                        if (!$dynamicTagDetected) {
                            $componentInfo = $this->components[$tagItem->Content];
                            $className = $componentInfo->Namespace . '\\' . $componentInfo->Name;
                            if ($this->components[$tagItem->Content]->Relative) {
                                include_once $this->sourcePath . $this->components[$tagItem->Content]->Fullpath;
                            } else {
                                include_once $this->components[$tagItem->Content]->Fullpath;
                            }
                        }
                        // if ($tagItem->Content == 'HelloMessage') {
                        //     $this->debug($tagItem->Content . ': ' . $childTag->Content . '=' . $values[0]->Content);
                        //     $this->debug($className);
                        // }
                        if ($dynamicTagDetected || class_exists($className)) {
                            //$this->debug($className);

                            if (!$dynamicTagDetected) {
                                if (!isset($this->components[$tagItem->Content]->Inputs)) {
                                    $this->components[$tagItem->Content]->Inputs = [];
                                }
                                $reflect = new ReflectionClass($className);
                                $props = $reflect->getProperties(ReflectionProperty::IS_PUBLIC);
                                $propsMap = [];
                                foreach ($props as $propertyInfo) {
                                    $propsMap[$propertyInfo->getName()] = true; // TODO: check for type ?
                                }
                            }
                            $inputArgument = $childTag->Content;
                            if ($dynamicTagDetected || isset($propsMap[$inputArgument])) {
                                if (
                                    !$dynamicTagDetected
                                    && !isset($this->components[$tagItem->Content]->Inputs[$inputArgument])
                                ) {
                                    $this->components[$tagItem->Content]->Inputs[$inputArgument] = 1;
                                }
                                //$inputValue = $this->getChildValues($childTag);

                                $inputValue = $this->combineChildren(
                                    $childTag,
                                    $values[0]->ItsExpression || count($values) > 1,
                                    [],
                                    false,
                                    true,
                                    false
                                );

                                // if (
                                //     strpos($inputValue, '(') === false
                                //     && $inputValue[0] !== '$'
                                //     && !ctype_digit($inputValue)
                                //     && $inputValue !== 'true'
                                //     && $inputValue !== 'false'
                                // ) { // its a string
                                //     $inputValue = str_replace("'", "\\'", $inputValue);
                                //     $inputValue = "'$inputValue'";
                                // }
                                // $this->debug($inputValue);
                                $inputValue = $this->convertExpressionToCode($inputValue);
                                // $this->debug($inputValue);
                                if ($inputValue === "'true'") {
                                    $inputValue = 'true';
                                } else if ($inputValue === "'false'") {
                                    $inputValue = 'false';
                                } else if (ctype_digit(str_replace("'", "", $inputValue))) {
                                    $inputValue = (float)str_replace("'", "", $inputValue);
                                } else if ($inputValue && substr($inputValue, 0, 2) == '\'[') { // array
                                    // $this->debug($inputValue);
                                    $inputValue = str_replace("\\'", "'", substr($inputValue, 1, strlen($inputValue) - 2));
                                    // $inputValue = eval("return $inputValue;");
                                    // $this->debug($inputValue);
                                }
                                $inputArguments[$inputArgument] = $inputValue;
                                // $this->debug($inputArgument);
                                // $this->debug($inputValue);
                                // $this->debug($propsMap);
                            }
                        }
                    }
                }
            }
            //$this->debug($inputArguments, true);
            // compile component
            if ($codeToAppend) {
                if ($this->renderReturn) {
                    $html .= PHP_EOL . $this->indentation . "\$_content .= " .
                        var_export($codeToAppend, true) . ";";
                } else {
                    $html .= $codeToAppend;
                }
                $codeToAppend = '';
            }
            $this->compileComponentExpression($tagItem, $html, null, $inputArguments);
            $this->extraLine = true;

            // add template versions into the queue
            if (!$dynamicTagDetected) {
                $componentInfo = $this->components[$tagItem->Content];
                if ($componentInfo->HasVersions) {
                    if (!isset($this->templateVersions[$tagItem->Content])) {
                        $this->templateVersions[$tagItem->Content] = [];
                    }
                    $this->templateVersions[$tagItem->Content][] = $inputArguments;
                    // $this->debug($this->templateVersions);
                }
            }
            if ($dynamicTagDetected) {
                // put if
                if ($this->renderReturn) {
                    $html .= PHP_EOL . $this->indentation . "\$_content .= " .
                        var_export($codeToAppend, true) . ";";
                    $codeToAppend = '';
                    $html .= PHP_EOL . $this->indentation . "}";
                } else {
                    $codeToAppend .= '<?php' . PHP_EOL . $this->indentation .
                        '}' . PHP_EOL . '?>';
                }
            }
        }
        // =========================================================================
        // if ($codeToAppend) {
        //     if ($this->renderReturn) {
        //         $html .= PHP_EOL . $this->identation . "\$_content .= " .
        //             var_export($codeToAppend, true) . ";";
        //     } else {
        //         $html .= $codeToAppend;
        //     }
        //     $codeToAppend = '';
        // }
        if ($ifExpression && $firstFound === 'if') {
            $this->closeIf($ifExpression, $html, $codeToAppend, $closeIfTag);
        }
        $this->endForeach($foreach, $foreachExpression, $foreachArguments, $html, $codeToAppend);
        if ($ifExpression && $firstFound !== 'if') {
            $this->closeIf($ifExpression, $html, $codeToAppend, $closeIfTag);
        }
        $this->closeElseIf($elseIfExpression, $html, $codeToAppend, $closeIfTag);
        $this->closeElse($elseExpression, $html, $codeToAppend);
        $this->previousItem = $tagItem;
    }

    function compileTemplate(ComponentInfo $componentInfo): PageTemplate
    {
        $template = new PageTemplate();
        // $this->debug($componentInfo);
        $path = $componentInfo->Relative ? $this->sourcePath . $componentInfo->TemplatePath : $componentInfo->TemplatePath;
        if (empty($path)) {
            throw new Exception("Argument `\$path` is missing");
        }
        $template->Path = $path;
        $template->ComponentInfo = $componentInfo;
        $text = file_get_contents($path);
        $raw = str_split($text);
        $template->RootTag = new TagItem();
        $currentParent = &$template->RootTag;
        $currentType = new TagItemType(TagItemType::TextContent);
        $nextType = new TagItemType(TagItemType::TextContent);
        $content = '';
        $saveContent = false;
        $nextIsExpression = false;
        $itsExpression = false;
        $itsBlockExpression = false;
        $blocksCount = 0;
        $skipInExpression = 0;
        $detectedQuoteChar = false;
        $skipCount = 0;
        $length = count($raw);
        $i = 0;
        $goDown = false;
        $goUp = false;
        $waitForTagEnd = false;
        $escapeNextChar = false; // $ < > { }
        while ($i < $length) {
            $char = $raw[$i];
            if (!$itsBlockExpression) {
                switch ($char) {
                    case '\\': {
                            $escapeNextChar = true;
                            $skipCount = 1;
                            break;
                        }
                    case '<': {
                            if ($escapeNextChar) {
                                $escapeNextChar = false;
                                break;
                            }
                            if ($currentType->Name === TagItemType::TextContent) {
                                if (
                                    $i + 1 < $length // there is still some content
                                    && (ctype_alpha($raw[$i + 1]) //any letter
                                        || $raw[$i + 1] === '$' // dynamic tag
                                        || $raw[$i + 1] === '/') // self closing tag
                                ) {
                                    // it's a tag
                                    $nextType = new TagItemType(TagItemType::Tag);
                                    $skipCount = 1;
                                    $saveContent = true;
                                    break;
                                }
                                if (
                                    $i + 3 < $length // there is still some content
                                    && $raw[$i + 1] === '!'
                                    && $raw[$i + 2] === '-' // comment
                                    && $raw[$i + 3] === '-' // comment
                                ) {
                                    // it's a tag
                                    $nextType = new TagItemType(TagItemType::Comment);
                                    $skipCount = 4;
                                    $saveContent = true;
                                    break;
                                }
                                break;
                            }
                            break;
                        }
                    case '-': {
                            if (
                                $currentType->Name === TagItemType::Comment
                                && $i + 2 < $length // there is still some content
                                && $raw[$i + 1] === '-'
                                && $raw[$i + 2] === '>' // end of comment
                            ) {
                                $skipCount = 3;
                                $nextType = new TagItemType(TagItemType::TextContent);
                                $saveContent = true;
                            }
                            break;
                        }
                    case '>': {
                            if (
                                !$waitForTagEnd
                                && $currentType->Name === TagItemType::Attribute
                                && isset($this->voidTags[$currentParent->Content])
                            ) {
                                $skipCount = 1;
                                $nextType = new TagItemType(TagItemType::TextContent);
                                $goUp = $currentType->Name !== TagItemType::Tag;
                                $saveContent = true;
                                break;
                            }

                            if (
                                !$waitForTagEnd
                                && $currentType->Name === TagItemType::Tag
                                && isset($this->voidTags[$content])
                            ) {
                                $skipCount = 1;
                                $nextType = new TagItemType(TagItemType::TextContent);
                                $saveContent = true;
                                break;
                            }

                            if (
                                $currentType->Name === TagItemType::AttributeValue
                                || $currentType->Name === TagItemType::Comment
                            ) {
                                break;
                            }
                            if ($escapeNextChar) {
                                $escapeNextChar = false;
                                break;
                            }
                            if ($waitForTagEnd) {
                                $waitForTagEnd = false;
                                $skipCount = 1;
                                $nextType = new TagItemType(TagItemType::TextContent);
                                $goUp = true;
                                $saveContent = true;
                                break;
                            }
                            if ($currentType->Name !== TagItemType::TextContent) {
                                $nextType = new TagItemType(TagItemType::TextContent);
                                $skipCount = 1;
                                $saveContent = true;

                                if ($currentType->Name === TagItemType::Tag) {
                                    $goDown = true;
                                }
                                break;
                            }
                            break;
                        }
                    case '/': {
                            if ($escapeNextChar) {
                                $escapeNextChar = false;
                                break;
                            }
                            if ($currentType->Name === TagItemType::Tag) { // <tag/> or </tag>
                                $skipCount = 1;
                                if ($content === '' || ctype_space($content)) { // </tag> closing tag
                                    // ignore next until '>'
                                    $waitForTagEnd = true;
                                } else { // <tag/> selfClosingTag
                                    $nextType = new TagItemType(TagItemType::TextContent);
                                    $skipCount = 1;
                                    $saveContent = true;
                                    $waitForTagEnd = true;
                                    $goDown = true;
                                }
                                break;
                            }
                            //<tag attr.. /> or <tag />
                            if ($currentType->Name === TagItemType::Attribute) {
                                $skipCount = 1;
                                $waitForTagEnd = true;
                                $saveContent = true;
                            }
                            break;
                        }
                    case '=': {
                            if ($escapeNextChar) {
                                $escapeNextChar = false;
                                break;
                            }
                            if ($currentType->Name === TagItemType::Attribute) {
                                $skipCount = 1;
                                $saveContent = true;
                                $nextType = new TagItemType(TagItemType::AttributeValue);
                                $goDown = true;
                            }
                            break;
                        }
                    case "'":
                    case '"': {
                            if ($escapeNextChar) {
                                $escapeNextChar = false;
                                break;
                            }
                            if ($currentType->Name === TagItemType::AttributeValue) {
                                if ($detectedQuoteChar) {
                                    if ($detectedQuoteChar === $char) { // end of value, closing quote " or '
                                        $detectedQuoteChar = false;
                                        $saveContent = true;
                                        $nextType = new TagItemType(TagItemType::Attribute);
                                        $goUp = true;
                                        $skipCount = 1;
                                    }
                                } else { // begin "attr value"
                                    $detectedQuoteChar = $char;
                                    $skipCount = 1;
                                }
                            }
                            break;
                        }
                    case '}': {
                            if ($escapeNextChar) {
                                $escapeNextChar = false;
                            }
                            break;
                        }
                    case '{': {
                            if ($escapeNextChar) {
                                $escapeNextChar = false;
                                break;
                            }
                            // allow inline style and scripts for a moment
                            if (
                                ($currentParent->Content === 'style'
                                    || $currentParent->Content === 'script')
                                && $currentParent->Type->Name === TagItemType::Tag
                            ) {
                                break;
                            }
                            $itsBlockExpression = true;
                            $skipCount = 1;
                            $skipInExpression = 0;
                            $saveContent = true;
                            $nextIsExpression = true;
                            $saveContent = true;
                            break;
                        }
                    case '$': {
                            if ($escapeNextChar) {
                                $escapeNextChar = false;
                                break;
                            }
                            $nextIsExpression = true;
                            $saveContent = true;
                            break;
                        }
                    default: {
                            if ($escapeNextChar) { // no escaping matched
                                $escapeNextChar = false;
                                $content .= '\\'; // returning back 
                            }
                            if (ctype_space($char)) {
                                if (
                                    $currentType->Name === TagItemType::Tag
                                    || $currentType->Name === TagItemType::Attribute
                                ) { // '<tag attribute="value"'
                                    $skipCount = 1;
                                    $nextType = new TagItemType(TagItemType::Attribute);
                                    $saveContent = true;
                                    if ($currentType->Name === TagItemType::Tag) {
                                        $goDown = true;
                                    }
                                    break;
                                }
                            }
                            if ($itsExpression) {
                                if (!ctype_alnum($char)) {
                                    $saveContent = true;
                                }
                            }
                        }
                } // end of switch
            } else { // $itsBlockExpression === true
                if ($skipInExpression > 0) {
                    $skipInExpression--;
                } else {
                    switch ($char) {
                        case '{': {
                                $blocksCount++;
                                // $this->debug($blocksCount);
                                break;
                            }
                        case '}': {
                                if ($blocksCount > 0) {
                                    $blocksCount--;
                                } else { // end of expression
                                    $itsBlockExpression = false;
                                    $skipCount = 1;
                                    $saveContent = true;
                                    // $this->debug($content);
                                }
                                break;
                            }
                    }
                }
            }
            if ($waitForTagEnd) {
                $skipCount = 1;
            }
            if ($saveContent) {
                if ($content !== '') {
                    $child = $currentParent->newChild();
                    $child->Type = $currentType;
                    $child->Content = $content;
                    $child->ItsExpression = $itsExpression;
                    if ($currentType->Name === TagItemType::Tag && !$itsExpression) {
                        if (
                            !strpos($content, ':')
                            && !isset($this->reservedTags[strtolower($content)])
                        ) {
                            if (!isset($this->components[$content])) {
                                throw new Exception("Component `$content` not found.");
                            }

                            $child->Type = new TagItemType(TagItemType::Component);
                        }
                    }
                }
                $itsExpression = false;
                if ($nextIsExpression) {
                    $nextIsExpression = false;
                    $itsExpression = true;
                }
                $saveContent = false;
                $currentType = $nextType;
                $content = '';
                if ($goDown && !$goUp) {
                    if ($currentParent->getChildren()) {
                        $currentParent = &$currentParent->currentChild();
                    } else {
                        echo 'Can\'t get child, silent exit';
                        break;
                    }
                }
                if ($goUp && !$goDown) {
                    if ($currentParent->parent()) {
                        $currentParent = &$currentParent->parent();
                    } else {
                        echo 'Can\'t get parent, silent exit';
                        break;
                    }
                }
                $goDown = false;
                $goUp = false;
            }


            if ($skipCount > 0) {
                $skipCount--;
            } else {
                $content .= $char;
            }
            // end of while
            $i++;
        }
        if ($content !== '') {
            $child = $currentParent->newChild();
            $child->Type = $currentType;
            $child->Content = $content;
            $child->ItsExpression = $itsExpression;
            if ($currentType->Name === TagItemType::Tag && !$itsExpression) {
                if (
                    !strpos($content, ':')
                    && !isset($this->reservedTags[strtolower($content)])
                ) {
                    if (!isset($this->components[$content])) {
                        throw new Exception("Component `$content` not found.");
                    }

                    $child->Type = new TagItemType(TagItemType::Component);
                }
            }
        }
        return $template;
    }

    function debug($any, bool $checkEmpty = false): void
    {
        if ($checkEmpty && empty($any)) {
            return;
        }
        echo '<pre>';
        echo htmlentities(print_r($any, true));
        echo '</pre>';
    }

    function getDirContents($dir, &$results = array())
    {
        $files = scandir($dir);

        foreach ($files as $key => $value) {
            $path = realpath($dir . DIRECTORY_SEPARATOR . $value);
            if (!is_dir($path)) {
                $results[$path] = true;
            } else if ($value != "." && $value != "..") {
                $this->getDirContents($path, $results);
            }
        }

        return $results;
    }
}
