<?php

require 'TagItemType.php';
require 'BaseComponent.php';
require 'BaseService.php';
require 'ComponentInfo.php';
require 'ComponentRenderer.php';
require 'PageTemplate.php';
require 'TagItem.php';

class PageEngine
{
    private string $sourcePath;
    private string $buildPath;
    private ?PageTemplate $latestPageTemplate = null;
    private int $slotCounter = 0;

    /** @var ComponentInfo[] */
    private array $components;

    /** @var mixed[] */
    private array $tokens;

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

    /** @var string<string,string> */
    private array $selfClosingTags;

    private string $selfClosingTagsString = 'area,base,br,col,command,embed,hr' .
        ',img,input,keygen,link,menuitem,meta,param,source,track,wbr';

    /** @var string<string,string> */
    private array $booleanAttributes;
    private string $booleanAttributesString = 'async,autofocus,autoplay,checked,controls,' .
        'default,defer,disabled,formnovalidate,hidden,ismap,itemscope,loop,' .
        'multiple,muted,nomodule,novalidate,open,readonly,required,reversed,' .
        'selected';

    private bool $extraLine = false;
    private bool $development;
    private array $componentArguments = [];
    private bool $compiled = false;
    public function __construct(string $sourcePath, string $buildPath, bool $development)
    {
        $this->sourcePath = $sourcePath;
        $this->buildPath = $buildPath;
        $this->components = [];
        $this->tokens = [];
        $this->templates = [];
        $this->development = $development;
        $this->reservedTags = array_flip(explode(',', $this->reservedTagsString));
        $this->selfClosingTags = array_flip(explode(',', $this->selfClosingTagsString));
        $this->booleanAttributes = array_flip(explode(',', $this->booleanAttributesString));
    }

    function render(string $component)
    {
        if ($this->development) {
            $this->Compile();
        } else {
            // include component infos
            $componentsPath = $this->buildPath . DIRECTORY_SEPARATOR . 'components.php';
            include_once $componentsPath;
            ReadComponentsInfo($this);
        }

        if (!isset($this->components[$component])) {
            throw new Exception("Component {$component} is missing!");
        }
        $this->renderComponent($component, null, [], []);
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

    /**
     * 
     * @param ReflectionClass $reflectionClass 
     * @return void 
     */
    function buildDependencies(ReflectionClass $reflectionClass): void
    {
        $name = $reflectionClass->name;
        if (!isset($this->components[$name])) {
            $componentInfo = new ComponentInfo();
            $componentInfo->Name = $name;
            $componentInfo->IsComponent = false;
            $componentInfo->Fullpath = $reflectionClass->getFileName();
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
                                    'name' => $namedType->getName()
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
    /** */
    function Compile(): void
    {
        if ($this->compiled) {
            return;
        }
        $this->compiled = true;
        $this->removeDirectory($this->buildPath);
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
            $componentInfo = new ComponentInfo();
            $className = $reflectionClass->name;
            $filename = $reflectionClass->getFileName();
            $dependencies = $this->getDependencies($reflectionClass);
            if (!empty($dependencies)) {
                $componentInfo->Dependencies = $dependencies;
            }
            $pathinfo = pathinfo($filename);
            $pathWOext = $pathinfo['dirname'] . DIRECTORY_SEPARATOR . $pathinfo['filename'];
            $templatePath = $pathWOext . '.html';
            $componentInfo->IsComponent = true;
            $componentInfo->Fullpath = $filename;
            if (isset($pages[$templatePath])) {
                $componentInfo->TemplatePath = $templatePath;
            }
            $componentInfo->Name = $className;
            $componentInfo->ComponentName = $className;
            $componentInfo->Tag = $className;

            if (!empty($className)) {
                $this->components[$className] = $componentInfo;
                $this->tokens[$className] = $tokens;
            }
        }
        $types = $this->getClasses(null, $this->sourcePath);
        foreach ($types as $filename => &$reflectionClass) {
            $this->buildDependencies($reflectionClass);
        }
        //$this->debug($this->sourcePath);
        //$this->debug($this->buildPath);
        foreach ($this->components as $className => &$componentInfo) {
            if ($componentInfo->IsComponent) {
                $this->templates[$className] = $this->compileTemplate($componentInfo);
                $this->build($this->templates[$className]);
                $this->save($this->templates[$className]);
            }
        }
        $componentsPath = $this->buildPath . DIRECTORY_SEPARATOR . 'components.php';
        $content = var_export(json_decode(json_encode($this->components), true), true);
        $componentsInfoTemplate = __DIR__ . DIRECTORY_SEPARATOR . 'ComponentsInfoTemplate.php';
        $templateContent = file_get_contents($componentsInfoTemplate);
        $parts = explode("//#content", $templateContent, 2);
        $content = $parts[0] . '$pageEngine->setComponentsInfo(' . $content . ');' . $parts[1]; // $pageEngine
        file_put_contents($componentsPath, $content);
        foreach ($this->templates as &$template) {
            $template->RootTag->cleanParents();
        }

        //$this->debug($this->components);
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
        $buildFilePath = str_replace($this->sourcePath, $buildPath, $pageTemplate->Path);
        $pathinfo = pathinfo($buildFilePath);
        //$this->debug($pageTemplate->Path);
        //$this->debug($pathinfo);
        if (!file_exists($pathinfo['dirname'])) {
            mkdir($pathinfo['dirname'], 0777, true);
        }
        $pathWOext = $pathinfo['dirname'] . DIRECTORY_SEPARATOR . $pathinfo['filename'];
        $phpPath = $pathWOext . '.php';
        file_put_contents($phpPath, $pageTemplate->PhpHtmlContent);
        $pageTemplate->ComponentInfo->BuildPath = $phpPath;
    }

    function build(PageTemplate &$pageTemplate): void
    {
        $moduleTemplatePath = __DIR__ . DIRECTORY_SEPARATOR . 'ComponentModuleTemplate.php';
        $moduleContent = file_get_contents($moduleTemplatePath);
        $parts = explode("//#content", $moduleContent, 2);
        $html = $parts[0];
        $renderFunction = "Render{$pageTemplate->ComponentInfo->Name}";
        $html = str_replace('BaseComponent', $pageTemplate->ComponentInfo->ComponentName, $html);
        $html = str_replace('RenderFunction', $renderFunction, $html);

        $scopeArguments = implode(', ', $this->componentArguments);
        if ($scopeArguments) {
            $scopeArguments = ', ' . $scopeArguments;
        } else {
            $scopeArguments = ', ...$scope';
        }
        $html = str_replace('/** scope*/', $scopeArguments, $html);
        // 
        $html .= '?>';
        $this->buildInternal($pageTemplate, $html);
        $html .= '<?php' . $parts[1];
        $pageTemplate->PhpHtmlContent = $html;
        $pageTemplate->ComponentInfo->RenderFunction = $renderFunction;
        //$this->debug(htmlentities($html));
        //$this->debug(json_encode($pageTemplate, JSON_PRETTY_PRINT));
    }
    private function buildInternal(PageTemplate &$pageTemplate, string &$html): void
    {
        $previousPageTemplate = $this->latestPageTemplate;
        $this->latestPageTemplate = $pageTemplate;
        foreach ($pageTemplate->RootTag->getChildren() as &$tag) {
            $this->buildTag($tag, $html);
        }
        $this->latestPageTemplate = $previousPageTemplate;
    }

    function convertExpressionToCode(string $expression): string
    {
        if (!empty($this->componentArguments)) { // exclude variables in scope
            $parts1 = explode(' ', $expression);
            foreach ($parts1 as &$item1) {
                $parts2 = explode('=>', $item1);
                foreach ($parts2 as &$item2) {
                    $parts3 = explode('(', $item2);
                    foreach ($parts3 as &$item3) {
                        $parts4 = explode('->', $item3, 2);
                        foreach ($parts4 as &$item4) {
                            if (strpos($item4, '$') === 0) {
                                if (!isset($this->componentArguments[$item4])) {
                                    $item4 = '$component->' . substr($item4, 1);
                                }
                            }
                            break;
                        }
                        $item3 = implode('->', $parts4);
                    }
                    $item2 = implode('(', $parts3);
                }
                $item1 = implode('=>', $parts2);
            }
            //$this->debug($expression);
            $expression = implode(' ', $parts1);
            //$this->debug($expression);
        } else {
            $expression = implode('$component->', explode('$', $expression));
        }
        if (strpos($expression, '(') !== false) {
            $raw = str_split($expression);
            $count = count($raw);
            $methodBegin = false;
            $beginings = [];
            for ($i = 0; $i < $count; $i++) {
                $char = $raw[$i];
                if ($char === '(') {
                    if ($methodBegin !== false) {
                        // validate and add
                        $beginings[] = $methodBegin;
                        $methodBegin = false;
                    }
                }

                if (ctype_alnum($char) || $char === '-' || $char === '>') {
                    if ($methodBegin === false) {
                        if (ctype_alpha($char)) {
                            $methodBegin = $i;
                        }
                    }
                } else {
                    $methodBegin = false;
                }
            }
            $beginings = array_reverse($beginings);
            foreach ($beginings as $pos) {
                $expression = substr_replace($expression, '$component->', $pos, 0);
            }
        }
        return $expression;
    }

    function compileExpression(string $expression, $class = null): string // TODO: validate expression
    {
        if ($expression[0] === '{' && $expression[strlen($expression) - 1] === '}') {
            $code = '<?=';
            $code .= $this->convertExpressionToCode(substr($expression, 1, strlen($expression) - 2));
            $code .= '?>';
        } else {
            $code = '<?=htmlentities(';
            $code .= $this->convertExpressionToCode($expression);
            $code .= ')?>';
        }
        return $code;
    }
    /**
     * 
     * @var array<string,object>
     */
    private array $Dependencies = [];
    function resolve(ComponentInfo &$componentInfo, bool $defaultCache = false)
    {
        $cache = true;
        if ($componentInfo->IsComponent) {
            // always new instance
            $cache = $defaultCache;
            include_once $componentInfo->Fullpath;
            include_once $componentInfo->BuildPath;
        } elseif (isset($componentInfo->IsSlot)) {
            include_once $componentInfo->Fullpath;
            include_once $componentInfo->BuildPath;
            return $this->resolve($this->components[$componentInfo->ComponentName], true);
        } else {
            // It's service or any class (It's not component)
            include_once $componentInfo->Fullpath;
        }
        $class = $componentInfo->Name;
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
            ($this->components[$componentName]->RenderFunction)($classInstance, $this, $slots, ...$slotArguments);
        }
    }

    function compileComponentExpression(TagItem $tagItem, string &$html, ?string $slotName = null, array $inputArguments = []): void
    {
        // generate slot(s)
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
            $slotPageTemplate->ComponentInfo->Tag = $componentBaseName;
            //$this->debug($this->latestPageTemplate->ComponentInfo);
            $pathinfo = pathinfo($this->latestPageTemplate->ComponentInfo->Fullpath);
            $pathWOext = $pathinfo['dirname'] . DIRECTORY_SEPARATOR . $componentBaseName;
            $phpPath = $pathWOext . '.php';
            $htmlPath = $pathWOext . '.html';
            $slotPageTemplate->ComponentInfo->TemplatePath = $htmlPath;
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
        if ($slotContentName) {
            $eol = PHP_EOL;
            $html .= "<?php$eol{$this->identation}\$slotContents[$slotContentName] = '{$componentBaseName}';$eol?>";
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

            $html .= "<?php" .
                ($componentBaseName ? PHP_EOL . $this->identation . "\$slotContents[0] = '$componentBaseName';" : '') .
                PHP_EOL . $this->identation . "\$pageEngine->renderComponent(" .
                "$componentName, " .
                "\$component, " .
                "\$slotContents, " .
                "$inputArgumentsCode" .
                "$scopeArguments);" .
                PHP_EOL . "?>";
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
            $html .= "<?php \$pageEngine->renderComponent($componentName, \$component, [], []); ?>";
        }
    }
    function startForeach(string $foreach, string &$html, array &$foreachArguments)
    {
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
        $html .= "<?php" . PHP_EOL . $this->identation .
            "foreach($foreachSource as {$foreachParts[1]}){" .
            PHP_EOL . $this->identation . "?>";
    }
    function endForeach($foreach, $foreachArguments, &$html)
    {
        if ($foreach) {
            $html .= "<?php" . PHP_EOL . $this->identation .
                "}" .
                PHP_EOL . $this->identation . "?>";
            foreach ($foreachArguments as $argument) {
                unset($this->componentArguments[$argument]);
            }
            // $this->debug($foreach);
            // $this->debug($foreachArguments);
            // $this->debug($this->componentArguments);
        }
    }
    function startIf(string $ifExpression, string &$html)
    {
        $ifCode = $this->convertExpressionToCode($ifExpression);
        $html .= "<?php" . PHP_EOL . $this->identation .
            "if($ifCode){" .
            PHP_EOL . $this->identation . "?>";
    }
    function closeIf(string $ifExpression, string &$html, bool $closeIfTag)
    {
        if ($ifExpression) {
            $html .= "<?php" . PHP_EOL . $this->identation .
                "}" .
                ($closeIfTag ? (PHP_EOL . $this->identation . "?>") : '');
        }
    }
    function startElseIf(string $elseIfExpression, string &$html)
    {
        $ifCode = $this->convertExpressionToCode($elseIfExpression);
        $html .= " else if ($ifCode){" .
            PHP_EOL . $this->identation . "?>";
    }
    function closeElseIf(string $elseIfExpression, string &$html, bool $closeIfTag)
    {
        if ($elseIfExpression) {
            $html .= "<?php" . PHP_EOL . $this->identation .
                "}" .
                ($closeIfTag ? PHP_EOL . $this->identation . "?>" : '');
        }
    }
    function startElse(string &$html)
    {
        $html .= " else {" .
            PHP_EOL . $this->identation . "?>";
    }
    function closeElse(string $elseExpression, string &$html)
    {
        if ($elseExpression) {
            $html .= "<?php" . PHP_EOL . $this->identation .
                "}" .
                PHP_EOL . $this->identation . "?>";
        }
    }
    function getCloseIfTag(TagItem &$tagItem): bool
    {
        $closeIfTag = true;
        $parentChilds = $tagItem->parent()->getChildren();
        $startLookingForElif = false;
        $textsToSkip = [];
        foreach ($parentChilds as &$parentChildren) {
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
                            foreach ($textsToSkip as &$textItem) {
                                $textItem->Skip = true;
                            }
                        }
                    }
                    break;
                } elseif ($parentChildren->Type->Name === TagItemType::TextContent) {
                    $textsToSkip[] = &$parentChildren;
                }
            }
        }
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
    function buildTag(TagItem &$tagItem, string &$html): void
    {
        $foreach = false;
        $ifExpression = false;
        $closeIfTag = true;
        $elseIfExpression = false;
        $elseExpression = false;
        $firstFound = false;
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
                    $foreach = '';
                    $ifItems = $childTag->getChildren();
                    foreach ($ifItems as &$ifValueItem) {
                        $foreach .= $ifValueItem->Content;
                    }
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
                    $ifExpression = '';
                    $ifItems = $childTag->getChildren();
                    foreach ($ifItems as &$ifValueItem) {
                        $ifExpression .= $ifValueItem->Content;
                    }
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
                    $elseIfExpression = '';
                    $ifItems = $childTag->getChildren();
                    foreach ($ifItems as &$ifValueItem) {
                        $elseIfExpression .= $ifValueItem->Content;
                    }
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
            $this->startElse($html);
        }
        if ($elseIfExpression) {
            $this->startElseIf($elseIfExpression, $html);
        }
        if ($ifExpression && $firstFound === 'if') {
            $this->startIf($ifExpression, $html);
        }
        if ($foreach) {
            $this->startForeach($foreach, $html, $foreachArguments);
        }
        if ($ifExpression && $firstFound !== 'if') {
            $this->startIf($ifExpression, $html);
        }

        if ($tagItem->Type->Name == TagItemType::Component) {

            $inputArguments = [];
            // extract slotContents and input arguments
            $children = $tagItem->getChildren();
            foreach ($children as &$childTag) {
                if (
                    $childTag->Type->Name === TagItemType::Tag
                    && $childTag->Content === 'slotContent'
                ) { // slot content
                    $this->compileComponentExpression($childTag, $html);
                } else if ($childTag->Type->Name === TagItemType::Attribute && !$childTag->Skip) {
                    $childTag->Skip = true; // component can't has attributes
                    // pass arguments
                    //$this->debug($tagItem->Content);
                    if (isset($this->components[$tagItem->Content])) {
                        $className = $this->components[$tagItem->Content]->ComponentName;
                        include_once $this->components[$tagItem->Content]->Fullpath;

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
            $this->compileComponentExpression($tagItem, $html, null, $inputArguments);
            $this->extraLine = true;
            if ($ifExpression && $firstFound === 'if') {
                $this->closeIf($ifExpression, $html, $closeIfTag);
            }
            $this->endForeach($foreach, $foreachArguments, $html);
            if ($ifExpression && $firstFound !== 'if') {
                $this->closeIf($ifExpression, $html, $closeIfTag);
            }
            $this->closeElseIf($elseIfExpression, $html, $closeIfTag);
            $this->closeElse($elseExpression, $html);
            return;
        }

        if ($tagItem->Type->Name == TagItemType::Tag) {
            if ($tagItem->ItsExpression) { // dynamic tag
                $this->compileComponentExpression($tagItem, $html);
                $this->extraLine = true;
                if ($ifExpression && $firstFound === 'if') {
                    $this->closeIf($ifExpression, $html, $closeIfTag);
                }
                $this->endForeach($foreach, $foreachArguments, $html);
                if ($ifExpression && $firstFound !== 'if') {
                    $this->closeIf($ifExpression, $html, $closeIfTag);
                }
                $this->closeElseIf($elseIfExpression, $html, $closeIfTag);
                $this->closeElse($elseExpression, $html);
                return;
            }
            if ($tagItem->Content === 'slot') { // render slot
                // $this->debug($tagItem);
                // foreach($tagItem->parent()->getChildren() as &$chld){
                //     var_dump($chld->Content);
                // }
                $this->compileSlotExpression($tagItem, $html);
                $this->extraLine = true;
                if ($ifExpression && $firstFound === 'if') {
                    $this->closeIf($ifExpression, $html, $closeIfTag);
                }
                $this->endForeach($foreach, $foreachArguments, $html);
                if ($ifExpression && $firstFound !== 'if') {
                    $this->closeIf($ifExpression, $html, $closeIfTag);
                }
                $this->closeElseIf($elseIfExpression, $html, $closeIfTag);
                $this->closeElse($elseExpression, $html);
                return;
            }
            if ($tagItem->Content === 'slotContent') { // render named slot (Component with named slots)
                // render like component but put slot names
                // create new function to render all children
                // put slot name in renderArgument
                //$this->compileComponentExpression($tagItem, $html);
                //$this->extraLine = true;
                //skip

                return;
            }
            //$html .= "<$replaceByTag data-component=\"{$content}\"";
        }
        if ($tagItem->Skip) {
            return;
        }
        $replaceByTag = 'div';
        $noChildren = empty($children);
        $noContent = true;
        $selfClosing = false;

        $content = $tagItem->ItsExpression
            ? $this->compileExpression($tagItem->Content)
            : $tagItem->Content;
        $skipTagRender = false;
        if ($tagItem->Type->Name == TagItemType::Tag) {
            $skipTagRender = $tagItem->Content === 'template';
            if (!$skipTagRender) {
                $html .= '<' . $content;
                if (isset($this->selfClosingTags[strtolower($content)])) {
                    $selfClosing = true;
                }
            }
            if (!$noChildren) { // merge attributes
                $newChildren = [];
                foreach ($children as &$childTag) {
                    if ($childTag->Type->Name === TagItemType::Attribute) {
                        if ($skipTagRender && !$childTag->Skip) { // template can't has attributes
                            trigger_error("`template` tag can't has attributes: attribute '{$childTag->Content}'", E_USER_WARNING);
                            continue;
                        }
                        $attributeName = $childTag->Content;
                        $mergeValues = $childTag->getChildren();

                        $valueToReplace = false;
                        if (strpos($attributeName, '.') !== false) {
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
            if ($this->extraLine) {
                $this->extraLine = false;
                if ($tagItem->Content[0] === "\n" || $tagItem->Content[0] === "\r") {
                    $html .= PHP_EOL;
                }
            }
            $html .= $content;
        } else {
            $this->extraLine = false;
        }
        $this->extraLine = $tagItem->ItsExpression;

        if ($tagItem->Type->Name === TagItemType::Attribute) {
            if (
                !$noChildren && count($children) == 1 && $children[0]->ItsExpression
                && isset($this->booleanAttributes[strtolower($tagItem->Content)])
            ) { // attribute is boolean, TODO: check argument expression to has boolean type
                // compile if based on expression
                $condition = $this->convertExpressionToCode($children[0]->Content);
                $html .= "<?=$condition ? ' {$tagItem->Content}=\"{$tagItem->Content}\"' : ''?>";
                return;
            }
            $html .= ' ' . $content . ($noChildren
                ? ''
                : '="');
        }

        if ($tagItem->Type->Name === TagItemType::AttributeValue) {
            $html .= $tagItem->ItsExpression ? $content : htmlentities($content);
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
                            $html .= '>';
                        }
                    }
                }
                $this->buildTag($childTag, $html);
            }
        }
        // END CHILDRENS scope
        if ($tagItem->Type->Name === TagItemType::Attribute) {
            $html .= ($noChildren ? '' : '"');
        }

        if ($tagItem->Type->Name === TagItemType::Tag) {
            if (!$skipTagRender) {
                if ($selfClosing) {
                    $html .= '/>';
                } else {
                    if ($noContent) {
                        $html .= '>';
                    }
                    $html .= '</' . $content . '>';
                    $this->extraLine = false;
                }
            }
        } else if ($tagItem->Type->Name === TagItemType::Component) {
            if ($noContent) {
                $html .= '>';
            }
            //render child component, TODO: replace by script
            //$component = $this->templates[$tagItem->Content];
            //$this->buildInternal($component, $html);
            $html .= "_<[||{$content}||]>_";
            $html .= "</$replaceByTag>";
        }

        if ($ifExpression && $firstFound === 'if') {
            $this->closeIf($ifExpression, $html, $closeIfTag);
        }
        $this->endForeach($foreach, $foreachArguments, $html);
        if ($ifExpression && $firstFound !== 'if') {
            $this->closeIf($ifExpression, $html, $closeIfTag);
        }
        $this->closeElseIf($elseIfExpression, $html, $closeIfTag);
        $this->closeElse($elseExpression, $html);
        // if ($tagItem->Type->Name == TagItemType::Expression) {
        //     $html .= "_(||{$tagItem->Content}||)_";
        // }
    }

    function compileTemplate(ComponentInfo $componentInfo): PageTemplate
    {
        $template = new PageTemplate();
        // $this->debug($componentInfo);
        $path = $componentInfo->TemplatePath;
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
        print_r($any);
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
