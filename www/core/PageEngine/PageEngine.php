<?php

namespace Vo;

use \ReflectionClass;
use \ReflectionProperty;
use \ReflectionNamedType;
use \Exception;

require 'DataExpression.php';
require 'TagItemType.php';
require 'BaseComponent.php';
require 'BaseService.php';
require 'ComponentInfo.php';
require 'ComponentRenderer.php';
require 'PageTemplate.php';
require 'TagItem.php';
require 'JsTranslator.php';

class PageEngine
{
    private string $sourcePath;
    private string $buildPath;
    private string $publicBuildPath;
    private ?PageTemplate $latestPageTemplate = null;
    private int $slotCounter = 0;

    /** @var ComponentInfo[] */
    private array $components;

    /** @var mixed[] */
    private array $tokens;
    /** @var string[] */
    private array $compiledJs;
    /** @var PageTemplate[] */
    private array $templates;

    /** @var string<string, string> */
    private array $reservedTags;
    private string $identation = '    ';

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
        'content,element,shadow,template,blockquote,iframe,tfoot' .
        'svg,animate,circle,clippath,cursor,defs,desc,ellipse,filter,font-face,' .
        'foreignObject,g,glyph,image,line,marker,mask,missing-glyph,path,pattern,' .
        'rect,switch,symbol,text,textpath,tspan,use,view,template,slot,slotcontent';

    /** @var array<string,string> */
    private array $selfClosingTags;

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
    private bool $compiled = false;
    private bool $waitingComponents = true;
    private JsTranslator $expressionsTranslator;
    /**
     * 
     * @var bool true: return string, false: echo
     */
    private bool $renderReturn;
    private string $_CompileComponentName = '$_component';
    private string $_CompileExpressionPrefix;
    public function __construct(
        string $sourcePath,
        string $buildPath,
        string $publicBuildPath,
        bool $development,
        bool $return = false
    ) {
        $this->sourcePath = $sourcePath;
        $this->buildPath = $buildPath;
        $this->publicBuildPath = $publicBuildPath;
        $this->renderReturn = $return;
        $this->components = [];
        $this->tokens = [];
        $this->templates = [];
        $this->development = $development;
        $this->reservedTags = array_flip(explode(',', $this->reservedTagsString));
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
    function render(string $component)
    {
        if ($this->development) {
            set_time_limit(2);
            $this->Compile();
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
        $content = $this->renderComponent($component, null, [], []);
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
            $componentInfo->Fullpath = $this->getRelativeSourcePath($reflectionClass->getFileName());
            $this->components[$name] = $componentInfo;
            $dependencies = $this->getDependencies($reflectionClass);
            if (!empty($dependencies)) {
                $componentInfo->Dependencies = $dependencies;
            }
        }
    }

    /**
     * 
     * @param ReflectionClass $reflectionClass 
     * @return array<array,string]>>
     */
    function getDependencies(ReflectionClass $reflectionClass): array
    {
        $dependencies = [];
        $constructor = $reflectionClass->getConstructor();
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
                            $argumentClass = $argument->getClass(); // check if class exists
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
                                $this->CompileToJs($argumentClass);
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

    function CompileToJs(ReflectionClass $reflectionClass): void
    {
        $className = $reflectionClass->getShortName();
        if (!isset($this->compiledJs[$className])) {
            $raw = file_get_contents($reflectionClass->getFileName());
            $translator = new JsTranslator($raw);
            $jscode = $translator->Convert();
            // $this->debug($className);
            // $this->debug($translator->GetVariablePathes());
            $this->compiledJs[$className] = $jscode;
        }
    }

    /** */
    function Compile(): void
    {
        if ($this->compiled) {
            return;
        }
        $this->_CompileExpressionPrefix = $this->_CompileComponentName . '->';
        $this->expressionsTranslator = new JsTranslator('');
        $this->expressionsTranslator->setOuterScope();
        $this->compiledJs = [];
        $this->compiled = true;
        $this->removeDirectory($this->buildPath);
        $this->removeDirectory($this->publicBuildPath);
        $pages = $this->getDirContents($this->sourcePath);
        foreach (array_keys($pages) as $filename) {
            $pathinfo = pathinfo($filename);
            if ($pathinfo['extension'] === 'php') {
                include_once $filename;
            }
        }
        $types = $this->getClasses(BaseComponent::class, $this->sourcePath);
        // $this->debug($this->sourcePath);
        // $this->debug($types);
        foreach ($types as $filename => &$reflectionClass) {
            // $this->debug('Path: '.$filename);
            $componentInfo = new ComponentInfo();
            $className = $reflectionClass->getShortName();
            $filename = $reflectionClass->getFileName();
            $dependencies = $this->getDependencies($reflectionClass);
            if (!empty($dependencies)) {
                $componentInfo->Dependencies = $dependencies;
            }
            $pathinfo = pathinfo($filename);
            $pathWOext = $pathinfo['dirname'] . DIRECTORY_SEPARATOR . $pathinfo['filename'];
            $templatePath = $pathWOext . '.html';
            $componentInfo->IsComponent = true;
            $componentInfo->Fullpath = $this->getRelativeSourcePath($filename);
            if (isset($pages[$templatePath])) {
                $componentInfo->TemplatePath = $this->getRelativeSourcePath($templatePath);
            }
            $componentInfo->Name = $className;
            $componentInfo->Namespace = $reflectionClass->getNamespaceName();
            $componentInfo->ComponentName = $className;
            $componentInfo->Tag = $className;

            if (!empty($className)) {
                $this->components[$className] = $componentInfo;
            }
            $this->CompileToJs($reflectionClass);
        }
        $types = $this->getClasses(null, $this->sourcePath);
        foreach ($types as $filename => &$reflectionClass) {
            $this->buildDependencies($reflectionClass);
        }
        //$this->debug($this->sourcePath);
        //$this->debug($this->buildPath);
        $publicJson = [];
        foreach ($this->components as $className => &$componentInfo) {


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
                        $publicJson[$className]['dependencies'][] = $argumentInfo;
                    }
                };
                if ($componentInfo->IsComponent) {
                    $publicJson[$className]['nodes'] = $this->templates[$className]->RootTag->getRaw();
                }
            }
        }
        // mate info
        $publicJson['_meta'] = ['tags' => $this->reservedTagsString];
        // $this->debug($this->templates);
        $componentsPath = $this->buildPath . DIRECTORY_SEPARATOR . 'components.php';
        $content = var_export(json_decode(json_encode($this->components), true), true);
        $componentsInfoTemplate = __DIR__ . DIRECTORY_SEPARATOR . 'ComponentsInfoTemplate.php';
        $templateContent = file_get_contents($componentsInfoTemplate);
        $parts = explode("//#content", $templateContent, 2);
        $content = $parts[0] . '$pageEngine->setComponentsInfo(' . $content . ');' . $parts[1]; // $pageEngine
        file_put_contents($componentsPath, $content);
        // save public json
        $publicFilePath = $this->publicBuildPath . DIRECTORY_SEPARATOR . 'components.json';
        $publicJsFilePath = $this->publicBuildPath . DIRECTORY_SEPARATOR . 'bundle.js';
        $publicJsonContent = json_encode($publicJson, 0, 1024);
        $publicBundleJs = implode('', array_values($this->compiledJs));
        file_put_contents($publicFilePath, $publicJsonContent);
        file_put_contents($publicJsFilePath, $publicBundleJs);
        //minify
        if ($this->_enableMinificationAndGzipping) {
            $publicBundleJsMin = $this->minify($publicBundleJs);
            file_put_contents($publicJsFilePath . '.min.js', $publicBundleJsMin);

            //gzip
            file_put_contents($publicFilePath . '.gz', gzencode($publicJsonContent, 5));
            file_put_contents($publicJsFilePath . '.min.js.gz', gzencode($publicBundleJsMin, 5));
        }
        //$this->debug($this->components);
    }
    private bool $_enableMinificationAndGzipping = false;
    function minify($js): string
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

    function save(PageTemplate &$pageTemplate)
    {
        $buildPath = $this->buildPath;
        if ($pageTemplate->ItsSlot) {
            $buildPath .= DIRECTORY_SEPARATOR . '_slots';
        }
        $buildFilePath = str_replace($this->sourcePath, $buildPath, $this->sourcePath . $this->getRelativeSourcePath($pageTemplate->Path));
        $pathinfo = pathinfo($buildFilePath);
        //$this->debug($pageTemplate->Path);
        //$this->debug($pathinfo);
        if (!file_exists($pathinfo['dirname'])) {
            mkdir($pathinfo['dirname'], 0777, true);
        }
        $pathWOext = $pathinfo['dirname'] . DIRECTORY_SEPARATOR . $pathinfo['filename'];
        $phpPath = $pathWOext . '.php';
        file_put_contents($phpPath, $pageTemplate->PhpHtmlContent);
        $pageTemplate->ComponentInfo->BuildPath = $this->getRelativeBuildPath($phpPath);
        $pageTemplate->RootTag->cleanParents();
    }

    function build(PageTemplate &$pageTemplate): void
    {
        $this->previousItem = new TagItem();
        $this->previousItem->Type = new TagItemType(TagItemType::TextContent);
        $moduleTemplatePath = __DIR__ . DIRECTORY_SEPARATOR . 'ComponentModuleTemplate.php';
        $moduleContent = file_get_contents($moduleTemplatePath);
        $parts = explode("//#content", $moduleContent, 2);
        $html = $parts[0];
        $renderFunction = "Render{$pageTemplate->ComponentInfo->Name}";
        $html = str_replace('BaseComponent $', $pageTemplate->ComponentInfo->Namespace . '\\' . $pageTemplate->ComponentInfo->ComponentName . ' $', $html);
        $html = str_replace('RenderFunction', $renderFunction, $html);

        $scopeArguments = implode(', ', $this->componentArguments);
        if ($scopeArguments) {
            $scopeArguments = ', ' . $scopeArguments;
        } else {
            $scopeArguments = ', ...$scope';
        }
        $html = str_replace('/** scope*/', $scopeArguments, $html);
        // 
        if ($this->renderReturn) {
            $html .= PHP_EOL . $this->identation . "\$_content = '';" . PHP_EOL;
        } else {
            $html .= '?>';
            if ($this->lastLineIsSpace) {
                $html .= PHP_EOL;
            }
        }
        $this->buildInternal($pageTemplate, $html);
        if ($this->renderReturn) {
            $html .= PHP_EOL . $this->identation . "return \$_content;" . PHP_EOL;
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
                $html .= PHP_EOL . $this->identation . "\$_content .= " .
                    var_export($codeToAppend, true) . ";";
            } else {
                $html .= $codeToAppend;
            }
        }
        $this->latestPageTemplate = $previousPageTemplate;
    }


    function convertExpressionToCode(string $expression, array $reserved = []): string
    {
        $keywordsList = $this->expressionsTranslator->GetKeywords($expression);
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
                    if (isset($this->componentArguments[$keyword]) || $newVariables) {
                        $newExpression .= $spaces[$i] . $keyword;
                    } else {
                        $newExpression .= $spaces[$i] . $this->_CompileExpressionPrefix . substr($keyword, 1);
                    }
                } else { // method or const or nested property
                    if ($i > 0 && $keywords[$i - 1] === '->') { // nested property or method
                        $newExpression .= $keyword;
                    } else if ($i + 1 < $count && $keywords[$i + 1] === '(') { // method call
                        $newExpression .= $this->_CompileExpressionPrefix . $keyword;
                    } else {
                        $newExpression .= $spaces[$i] . $keyword;
                    }
                }
            } else {
                $newExpression .= $spaces[$i] . $keyword;
            }
        }
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
            $tagItem->JsExpression = $this->expressionsTranslator->Convert($phpCode, true);
            $tagItem->RawHtml = true;
        } else {
            $code = ($this->renderReturn ? '' : '<?=') . 'htmlentities(';
            $phpCode = $this->convertExpressionToCode($expression, $reserved);
            $code .= $phpCode;
            $code .= ')' . ($this->renderReturn ? '' : '?>');
            $tagItem->JsExpression = $this->expressionsTranslator->Convert($phpCode, true);
            // $this->debug($phpCode . $tagItem->JsExpression);
        }
        $tagItem->PhpExpression = $phpCode;
        $detectedReferences = $this->expressionsTranslator->GetVariablePathes();
        if (isset($detectedReferences['global'])) {
            $subscriptions = array_map(
                function ($item) {
                    return 'this' . substr($item, strlen($this->_CompileExpressionPrefix) - 3);
                },
                array_keys($detectedReferences['global']['function'])
            );
            $tagItem->Subscriptions = $subscriptions;
        }
        // $this->debug($phpCode);
        // $this->debug($tagItem->JsExpression);
        // $this->debug($tagItem->Subscriptions);
        return $code;
    }
    /**
     * 
     * @var array<string,object>
     */
    private array $Dependencies = [];
    function resolve(ComponentInfo &$componentInfo, bool $defaultCache = false)
    {
        // cache service instances or parent components for slots
        // do not cache component instances
        $cache = true;
        if ($componentInfo->IsComponent) {
            // always new instance
            $cache = $defaultCache;
            include_once $this->sourcePath . $componentInfo->Fullpath;
            include_once $this->buildPath . $componentInfo->BuildPath;
        } elseif (isset($componentInfo->IsSlot)) {
            include_once $this->sourcePath . $componentInfo->Fullpath;
            include_once $this->buildPath . $componentInfo->BuildPath;
            return $this->resolve($this->components[$componentInfo->ComponentName], true);
        } else {
            // It's service or any class (It's not template component)
            include_once $this->sourcePath . $componentInfo->Fullpath;
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
        } else {
            $arguments = [];
            foreach ($componentInfo->Dependencies as $type) {
                if (isset($type['default'])) {
                    $arguments[] = $type['default'];
                } else if (isset($type['null'])) {
                    $arguments[] = null;
                } else if (isset($type['builtIn'])) {
                    switch ($type['name']) {
                        case 'string': {
                                $arguments[] = '';
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
            $instance = new $class(...$arguments);
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
        ?BaseComponent $parentComponent,
        array $slots,
        array $componentArguments,
        ...$slotArguments
    ) {
        //$this->debug($this->templates[$componentName]->ComponentInfo);
        if ($componentName) {
            $compInfo = &$this->components[$componentName];
            $classInstance = $this->resolve($compInfo);
            // TODO: reuse instance, TODO: dependency inject
            // init input properties
            // TODO: cache properties
            foreach ($componentArguments as $key => $inputValue) {
                if (isset($compInfo->Inputs[$key])) {
                    $classInstance->{$key} = $inputValue;
                }
            }
            return ($this->components[$componentName]->RenderFunction)($classInstance, $this, $slots, ...$slotArguments);
        }
    }

    function compileComponentExpression(TagItem $tagItem, string &$html, ?string $slotName = null, array $inputArguments = []): void
    {
        // generate slot(s)
        $lastLineIsSpace = false; // $this->lastLineIsSpace;
        $children = $tagItem->getChildren();
        $slots = [];
        $slotContentName = false;
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
                                $slotContentName = "'{$slotNameAttributeValues[0]->Content}'";
                                if ($slotNameAttributeValues[0]->ItsExpression) {
                                    $slotContentName = $this->convertExpressionToCode($slotNameAttributeValues[0]->Content);
                                }
                            }
                        }
                    }
                }
                $tagItem = $defaultTagItem;
            }

            $this->slotCounter++;
            $partialComponentName = $slotContentName ? '_SlotContent' : '_Slot';
            $componentBaseName = "{$this->latestPageTemplate->ComponentInfo->ComponentName}" .
                "$partialComponentName{$this->slotCounter}";
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

        if ($slotContentName) {

            $html .= "{$codeBegin}{$this->identation}\$slotContents[$slotContentName] = '{$componentBaseName}';$eol{$codeEnd}";
        } else {
            $scopeArguments = implode(', ', $this->componentArguments);
            if ($scopeArguments) {
                $scopeArguments = ', ' . $scopeArguments;
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
                $this->identation . "\$slotContents[0] = $slotsExpression;" .
                PHP_EOL . $this->identation . "{$codeMiddle}\$pageEngine->renderComponent(" .
                "$componentName, " .
                "{$this->_CompileComponentName}, " .
                "\$slotContents, " .
                "$inputArgumentsCode" .
                "$scopeArguments);" .
                PHP_EOL . $this->identation . "\$slotContents = [];" .
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
                            $componentName = "\$slots[$slotName]";
                        }
                    }
                }
            }
            if ($defaultContent) {
                $this->compileComponentExpression($defaultTagItem, $html, $componentName);
            }
        }
        if (!$defaultContent) {
            $codeBegin = $this->renderReturn ? PHP_EOL . $this->identation . "\$_content .=" : "<?php";
            $codeEnd = $this->renderReturn ? '' : '?>';
            $html .= "$codeBegin \$pageEngine->renderComponent($componentName, {$this->_CompileComponentName}, [], []); $codeEnd";
        }
    }
    function flushBuffer(string &$html, string &$codeToAppend)
    {
        if ($codeToAppend) {
            if ($this->renderReturn) {
                $html .= PHP_EOL . $this->identation . "\$_content .= " .
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
            $this->componentArguments[$argument] = $argument;
            $foreachArguments[$argument] = $argument;
        }
        $html .= ($this->renderReturn ? '' : "<?php") . PHP_EOL . $this->identation .
            "foreach($foreachSource as {$foreachParts[1]}){" .
            PHP_EOL . $this->identation . ($this->renderReturn ? '' : "?>");
    }
    function endForeach($foreach, $foreachArguments, &$html, string &$codeToAppend)
    {
        if ($foreach) {
            $this->flushBuffer($html, $codeToAppend);
            $html .= ($this->renderReturn ? '' : "<?php") . PHP_EOL . $this->identation .
                "}" .
                PHP_EOL . $this->identation . ($this->renderReturn ? '' : "?>");
            foreach ($foreachArguments as $argument) {
                unset($this->componentArguments[$argument]);
            }
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
        $html .= ($this->renderReturn ? '' : "<?php") . PHP_EOL . $this->identation .
            "if($ifCode){" .
            PHP_EOL . $this->identation . ($this->renderReturn ? '' : "?>");
    }
    function closeIf(string $ifExpression, string &$html, string &$codeToAppend, bool $closeIfTag)
    {
        if ($ifExpression) {
            $this->flushBuffer($html, $codeToAppend);
            $html .= ($this->renderReturn ? '' : "<?php") . PHP_EOL . $this->identation .
                "}" .
                ($closeIfTag ? (PHP_EOL . $this->identation . ($this->renderReturn ? '' : "?>")) : '');
            $this->extraLine = true;
        }
    }
    function startElseIf(string $elseIfExpression, string &$html, string &$codeToAppend)
    {
        $this->flushBuffer($html, $codeToAppend);
        $ifCode = $this->convertExpressionToCode($elseIfExpression);
        $html .= " else if ($ifCode){" .
            PHP_EOL . $this->identation . ($this->renderReturn ? '' : "?>");
    }
    function closeElseIf(string $elseIfExpression, string &$html, string &$codeToAppend, bool $closeIfTag)
    {
        if ($elseIfExpression) {
            $this->flushBuffer($html, $codeToAppend);
            $html .= ($this->renderReturn ? '' : "<?php") . PHP_EOL . $this->identation .
                "}" .
                ($closeIfTag ? PHP_EOL . $this->identation . ($this->renderReturn ? '' : "?>") : '');
            $this->extraLine = true;
        }
    }
    function startElse(string &$html, string &$codeToAppend)
    {
        $this->flushBuffer($html, $codeToAppend);
        $html .= " else {" .
            PHP_EOL . $this->identation . ($this->renderReturn ? '' : "?>");
    }
    function closeElse(string $elseExpression, string &$html, string &$codeToAppend)
    {
        if ($elseExpression) {
            $this->flushBuffer($html, $codeToAppend);
            $html .= ($this->renderReturn ? '' : "<?php") . PHP_EOL . $this->identation .
                "}" .
                PHP_EOL . $this->identation . ($this->renderReturn ? '' : "?>");
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

    private int $forIterationKey = 0;

    function GetNextIterationKey(): string
    {
        ++$this->forIterationKey;
        return "_key{$this->forIterationKey}";
    }

    function combineChildren(TagItem $childTag, bool $expression = true, array $reserved = [], bool $foreach = false): string
    {
        $attrValues = $childTag->getChildren();
        $newValueContent = '';
        foreach ($attrValues as $attrValue) {
            $newValueContent .= $attrValue->Content;
        }
        // replace children with one expression
        $newChild = $attrValues[0];
        $newChild->Content = $newValueContent;
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
                    $newChild->DataExpression->ForKey = $this->GetNextIterationKey();
                }
            }
        }
        return $newValueContent;
    }

    function buildTag(TagItem &$tagItem, string &$html, string &$codeToAppend): void
    {
        $foreach = false;
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
                // lines formating
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
                        $html .= PHP_EOL . $this->identation . "\$_content .= " .
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
                            $html .= PHP_EOL . $this->identation . "\$_content .= " .
                                var_export($codeToAppend, true) . ";";
                            $codeToAppend = '';
                            $html .= PHP_EOL . $this->identation .
                                "if(\$pageEngine->isTag($content)) {";
                        } else {
                            $codeToAppend .= '<?php' . PHP_EOL . $this->identation .
                                "if(\$pageEngine->isTag({$tagItem->PhpExpression})) {" . PHP_EOL . '?>';
                        }
                    }
                    $codeToAppend .= '<';
                    if ($this->renderReturn) {
                        if ($tagItem->ItsExpression) {
                            $html .= PHP_EOL . $this->identation . "\$_content .= " .
                                var_export($codeToAppend, true) . ";";
                            $html .= PHP_EOL . $this->identation . "\$_content .= " .
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
                            if ($attributeName[0] === '(') { // event
                                $childTag->Skip = true;
                                $attrValues = $childTag->getChildren();
                                $newValueContent = '';
                                foreach ($attrValues as $attrValue) {
                                    $newValueContent .= $attrValue->Content;
                                }
                                // replace children with one expression
                                $newChild = $attrValues[0];
                                $newChild->Content = $newValueContent;
                                $childTag->setChildren([$newChild]);
                                $newChild->ItsExpression = true;
                                $this->compileExpression($newChild, ['$event' => true]);
                            } else if (strpos($attributeName, '.') !== false) {
                                $parts = explode('.', $attributeName, 2);
                                $attributeName = $parts[0];
                                $valueToReplace = $parts[1];
                                $childTag->Content = $attributeName;
                            }
                            if (isset($newChildren[$attributeName])) { // merge values
                                $firstTime = true;
                                foreach ($mergeValues as &$attrValueItem) {
                                    if ($valueToReplace !== false) {
                                        $attrValueItem->Content = "{$attrValueItem->Content} ? ' $valueToReplace' : ''";
                                        $newChildren[$attributeName]->addChild($attrValueItem);
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
                                    }
                                }
                            } else {
                                if ($valueToReplace !== false) {
                                    $mergeValues[0]->Content = "{$mergeValues[0]->Content} ? '$valueToReplace' : ''";
                                }
                                $newChildren[$attributeName] = $childTag;
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

            if ($tagItem->Type->Name == TagItemType::TextContent) {
                if ($this->extraLine && !$this->renderReturn) {
                    $this->extraLine = false;
                    if ($tagItem->Content[0] === "\n" || $tagItem->Content[0] === "\r") {
                        $codeToAppend .= PHP_EOL;
                    }
                }
                if ($this->renderReturn && $tagItem->ItsExpression) {
                    $html .= PHP_EOL . $this->identation . "\$_content .= " .
                        var_export($codeToAppend, true) . ";";
                    $html .= PHP_EOL . $this->identation . "\$_content .= " .
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
                ) { // attribute is boolean, TODO: check argument expression to has boolean type
                    // compile if based on expression
                    $condition = $this->convertExpressionToCode($children[0]->Content);
                    if ($this->renderReturn) {
                        $this->flushBuffer($html, $codeToAppend);
                        $html .= PHP_EOL . $this->identation . "\$_content .= " .
                            "$condition ? ' {$tagItem->Content}=\"{$tagItem->Content}\"' : ''" . ";";
                    } else {
                        $html .= $codeToAppend;
                        $codeToAppend = '';
                        $html .= "<?=$condition ? ' {$tagItem->Content}=\"{$tagItem->Content}\"' : ''?>";
                        $this->previousItem = $tagItem;
                    }
                    return;
                }
                $codeToAppend .= ' ';
                if ($tagItem->ItsExpression && $this->renderReturn) {
                    $html .= PHP_EOL . $this->identation . "\$_content .= " .
                        var_export($codeToAppend, true) . ";";
                    $html .= PHP_EOL . $this->identation . "\$_content .= " .
                        $content . ";";
                    $codeToAppend = '';
                } else {
                    $codeToAppend .= $content;
                }

                $codeToAppend .=  ($noChildren
                    ? ''
                    : '="');
            }

            if ($tagItem->Type->Name === TagItemType::AttributeValue) {
                if ($tagItem->ItsExpression && $this->renderReturn) {
                    $html .= PHP_EOL . $this->identation . "\$_content .= " .
                        var_export($codeToAppend, true) . ";";
                    $html .= PHP_EOL . $this->identation . "\$_content .= " .
                        $content . ";";
                    $codeToAppend = '';
                } else {
                    $codeToAppend .= $tagItem->ItsExpression ? $content : htmlentities($content);
                }
            }
            // CHILDRENS scope
            if (!$noChildren) {
                foreach ($children as &$childTag) {
                    if (
                        $childTag->Type->Name === TagItemType::TextContent
                        || $childTag->Type->Name === TagItemType::Tag
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
            // END CHILDRENS scope
            if ($tagItem->Type->Name === TagItemType::Attribute) {
                $codeToAppend .= ($noChildren ? '' : '"');
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
                                $html .= PHP_EOL . $this->identation . "\$_content .= " .
                                    var_export($codeToAppend, true) . ";";
                                $html .= PHP_EOL . $this->identation . "\$_content .= " .
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
                            $html .= PHP_EOL . $this->identation . "\$_content .= " .
                                var_export($codeToAppend, true) . ";";
                            $codeToAppend = '';
                            $html .= PHP_EOL . $this->identation . "} else {";
                        } else {
                            $codeToAppend .= '<?php' . PHP_EOL . $this->identation .
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
            foreach ($children as &$childTag) {
                if (
                    $childTag->Type->Name === TagItemType::Tag
                    && $childTag->Content === 'slotContent'
                ) { // slot content
                    if ($codeToAppend) {
                        if ($this->renderReturn) {
                            $html .= PHP_EOL . $this->identation . "\$_content .= " .
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
                    //$this->debug($tagItem->Content);
                    if (isset($this->components[$tagItem->Content])) {
                        $className = $this->components[$tagItem->Content]->ComponentName;
                        include_once $this->sourcePath . $this->components[$tagItem->Content]->Fullpath;

                        if (class_exists($className)) {
                            //$this->debug($className);
                            if (!isset($this->components[$tagItem->Content]->Inputs)) {
                                $this->components[$tagItem->Content]->Inputs = [];
                            }
                            $reflect = new ReflectionClass($className);
                            $props = $reflect->getProperties(ReflectionProperty::IS_PUBLIC);
                            $propsMap = [];
                            foreach ($props as $propertyInfo) {
                                $propsMap[$propertyInfo->getName()] = true; // TODO: check for type ?
                            }
                            $inputArgument = $childTag->Content;
                            if (isset($propsMap[$inputArgument])) {
                                if (!isset($this->components[$tagItem->Content]->Inputs[$inputArgument])) {
                                    $this->components[$tagItem->Content]->Inputs[$inputArgument] = 1;
                                }
                                $inputValue = $this->getChildValues($childTag);
                                if (
                                    strpos($inputValue, '(') === false
                                    && $inputValue[0] !== '$'
                                    && !ctype_digit($inputValue)
                                    && $inputValue !== 'true'
                                    && $inputValue !== 'false'
                                ) { // its a string
                                    $inputValue = str_replace("'", "\\'", $inputValue);
                                    $inputValue = "'$inputValue'";
                                }
                                $inputValue = $this->convertExpressionToCode($inputValue);
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
                    $html .= PHP_EOL . $this->identation . "\$_content .= " .
                        var_export($codeToAppend, true) . ";";
                } else {
                    $html .= $codeToAppend;
                }
                $codeToAppend = '';
            }
            $this->compileComponentExpression($tagItem, $html, null, $inputArguments);
            $this->extraLine = true;

            if ($dynamicTagDetected) {
                // put if
                if ($this->renderReturn) {
                    $html .= PHP_EOL . $this->identation . "\$_content .= " .
                        var_export($codeToAppend, true) . ";";
                    $codeToAppend = '';
                    $html .= PHP_EOL . $this->identation . "}";
                } else {
                    $codeToAppend .= '<?php' . PHP_EOL . $this->identation .
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
        $this->endForeach($foreach, $foreachArguments, $html, $codeToAppend);
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
        $path = $this->sourcePath . $componentInfo->TemplatePath;
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
                                    $i + 1 < $length // there still some content
                                    && !ctype_alpha($raw[$i + 1]) //any letter
                                    && $raw[$i + 1] !== '$' // dynamic tag
                                    && $raw[$i + 1] !== '/' // self closing tag
                                ) {
                                    // it's not a tag
                                    break;
                                }
                                $nextType = new TagItemType(TagItemType::Tag);
                                $skipCount = 1;
                                $saveContent = true;
                                break;
                            }
                            break;
                        }
                    case '>': {
                            if ($currentType->Name === TagItemType::AttributeValue) {
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
                                if (empty($content) || ctype_space($content)) { // </tag> closing tag
                                    // ignore next untill '>'
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
                            // allow inline style for a moment
                            if (
                                $currentParent->Content === 'style'
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
                if (!empty($content)) {
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
