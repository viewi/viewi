<?php

require 'TagItemType.php';
require 'BaseComponent.php';
require 'ComponentInfo.php';
require 'ComponentRenderer.php';
require 'PageTemplate.php';
require 'TagItem.php';

class PageEngine
{
    private string $sourcePath;
    private string $buildPath;
    private string $rootComponent;
    private ?PageTemplate $latestPageTemplate = null;
    private int $slotCounter = 0;

    /** @var ComponentInfo[] */
    private array $components;

    /** @var mixed[] */
    private array $tokens;

    /** @var PageTemplate[] */
    private array $templates;

    /** @var string[string] */
    private array $reservedTags;

    private string $reservedTagsString = 'html,body,base,head,link,meta,style,title,' .
        'address,article,aside,footer,header,h1,h2,h3,h4,h5,h6,hgroup,nav,section,' .
        'div,dd,dl,dt,figcaption,figure,picture,hr,img,li,main,ol,p,pre,ul,' .
        'a,b,abbr,bdi,bdo,br,cite,code,data,dfn,em,i,kbd,mark,q,rp,rt,rtc,ruby,' .
        's,samp,small,span,strong,sub,sup,time,u,var,wbr,area,audio,map,track,video,' .
        'embed,object,param,source,canvas,script,noscript,del,ins,' .
        'caption,col,colgroup,table,thead,tbody,td,th,tr,' .
        'button,datalist,fieldset,form,input,label,legend,meter,optgroup,option,' .
        'output,progress,select,textarea,' .
        'details,dialog,menu,menuitem,summary,' .
        'content,element,shadow,template,blockquote,iframe,tfoot' .
        'svg,animate,circle,clippath,cursor,defs,desc,ellipse,filter,font-face,' .
        'foreignObject,g,glyph,image,line,marker,mask,missing-glyph,path,pattern,' .
        'polygon,polyline,rect,switch,symbol,text,textpath,tspan,use,view,template,slot';

    /** @var string[string] */
    private array $selfClosingTags;

    private string $selfClosingTagsString = 'area,base,br,col,command,embed,hr' .
        ',img,input,keygen,link,menuitem,meta,param,source,track,wbr';

    private bool $extraLine = false;

    public function __construct(string $sourcePath, string $buildPath, string $rootComponent)
    {
        $this->sourcePath = $sourcePath;
        $this->buildPath = $buildPath;
        $this->rootComponent = $rootComponent;
        $this->components = [];
        $this->tokens = [];
        $this->templates = [];
        $this->reservedTags = array_flip(explode(',', $this->reservedTagsString));
        $this->selfClosingTags = array_flip(explode(',', $this->selfClosingTagsString));
    }
    function startApp(): void
    {
        $this->Compile();
        if (!isset($this->components[$this->rootComponent])) {
            throw new Exception("Component {$this->rootComponent} is missing!");
        }
        $root = $this->components[$this->rootComponent];
        $rootApp = new ComponentRenderer();
        $rootApp->component = new $root->Name();

        //$this->debug($rootApp);
        //$this->debug($this->rootComponent . ' ' . $this->path);
        //$this->debug($this->templates);
        //$this->debug($this->components);
        //$this->debug($this->tokens);
    }

    function render()
    {
        $this->renderComponent($this->rootComponent, null, []);
    }

    function Compile(): void
    {
        $pages = $this->getDirContents($this->sourcePath);
        foreach (array_keys($pages) as $filename) {
            $pathinfo = pathinfo($filename);
            if ($pathinfo['extension'] === 'php') {
                $pathWOext = $pathinfo['dirname'] . DIRECTORY_SEPARATOR . $pathinfo['filename'];
                $templatePath = $pathWOext . '.html';
                $componentInfo = new ComponentInfo();
                $componentInfo->Fullpath = $filename;
                if (isset($pages[$templatePath])) {
                    $componentInfo->TemplatePath = $templatePath;
                }
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
                $componentInfo->Name = $className;
                $componentInfo->ComponentName = $className;
                $componentInfo->Tag = $className;

                if (!empty($className)) {
                    $this->components[$className] = $componentInfo;
                    $this->tokens[$className] = $tokens;
                }
            }
        }
        //$this->debug($this->sourcePath);
        //$this->debug($this->buildPath);
        foreach ($this->components as $className => &$componentInfo) {
            $this->templates[$className] = $this->compileTemplate($componentInfo);
            $this->build($this->templates[$className]);
            $this->save($this->templates[$className]);
        }
    }
    function save(PageTemplate &$pageTemplate)
    {
        $buildFilePath = str_replace($this->sourcePath, $this->buildPath, $pageTemplate->Path);
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
        $expression = implode('$component->', explode('$', $expression));
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
        $code = '<?=htmlentities(';
        $code .= $this->convertExpressionToCode($expression);
        $code .= ')?>';

        return $code;
    }

    function renderComponent(?string $componentName, ?BaseComponent $parentComponent, array $slots)
    {
        //$this->debug($this->templates[$componentName]->ComponentInfo);
        if ($componentName) {
            include_once $this->templates[$componentName]->ComponentInfo->Fullpath;
            include_once $this->templates[$componentName]->ComponentInfo->BuildPath;
            $pageClass = $this->templates[$componentName]->ComponentInfo->ComponentName;
            $classInstance = new $pageClass();
            ($this->templates[$componentName]->ComponentInfo->RenderFunction)($classInstance, $this, $slots);
        }
    }

    function compileComponentExpression(TagItem $tagItem, string &$html): void
    {
        // generate slot(s)
        $children = $tagItem->getChildren();
        $slots = [];
        if (!empty($children)) { // has slot(s)
            $this->slotCounter++;
            $name = "{$this->latestPageTemplate->ComponentInfo->ComponentName}Slot{$this->slotCounter}";
            $slotPageTemplate = new PageTemplate();
            $slotPageTemplate->RootTag = $tagItem;
            $slotPageTemplate->ComponentInfo = new ComponentInfo();
            $slotPageTemplate->ComponentInfo->Name = $name;
            $slotPageTemplate->ComponentInfo->ComponentName = $this->latestPageTemplate->ComponentInfo->ComponentName;
            $slotPageTemplate->ComponentInfo->Tag = $name;
            //$this->debug($this->latestPageTemplate->ComponentInfo);
            $pathinfo = pathinfo($this->latestPageTemplate->ComponentInfo->Fullpath);
            $pathWOext = $pathinfo['dirname'] . DIRECTORY_SEPARATOR . $name;
            $phpPath = $pathWOext . '.php';
            $htmlPath = $pathWOext . '.html';
            $slotPageTemplate->ComponentInfo->TemplatePath = $htmlPath;
            $slotPageTemplate->ComponentInfo->Fullpath = $this->latestPageTemplate->ComponentInfo->Fullpath;
            $slotPageTemplate->Path = $htmlPath;
            $this->templates[$name] = $slotPageTemplate;
            $this->build($this->templates[$name]);
            $this->save($this->templates[$name]);
            $slots[] = $name;
        }
        //render component
        $slotsArg = var_export($slots, true);
        $componentName = $tagItem->ItsExpression
            ? $this->convertExpressionToCode($tagItem->Content)
            : "'{$tagItem->Content}'";
        $html .= "<?php \$pageEngine->renderComponent($componentName, \$component, $slotsArg); ?>";
    }
    function compileSlotExpression(TagItem $tagItem, string &$html): void
    {
        $componentName = '$slots[0]';
        $html .= "<?php \$pageEngine->renderComponent($componentName, \$component, []); ?>";
    }

    function buildTag(TagItem &$tagItem, string &$html): void
    {
        if ($tagItem->Type->Name == TagItemType::Component) {
            $this->compileComponentExpression($tagItem, $html);
            $this->extraLine = true;
            return;
        }

        if ($tagItem->Type->Name == TagItemType::Tag) {
            if ($tagItem->ItsExpression) { // dynamic tag
                $this->compileComponentExpression($tagItem, $html);
                $this->extraLine = true;
                return;
            }
            if ($tagItem->Content === 'slot') { // render slot
                $this->compileSlotExpression($tagItem, $html);
                //$this->extraLine = true;
                return;
            }
            //$html .= "<$replaceByTag data-component=\"{$content}\"";
        }

        $replaceByTag = 'div';
        /** @var TagItem[] */
        $children = $tagItem->getChildren();
        $noChildren = empty($children);
        $noContent = true;
        $selfClosing = false;

        $content = $tagItem->ItsExpression
            ? $this->compileExpression($tagItem->Content)
            : $tagItem->Content;

        if ($tagItem->Type->Name == TagItemType::Tag) {
            $html .= '<' . $content;
            if (isset($this->selfClosingTags[strtolower($content)])) {
                $selfClosing = true;
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

        if ($tagItem->Type->Name == TagItemType::Attribute) {
            $html .= ' ' . $content . ($noChildren
                ? ''
                : '="');
        }

        if ($tagItem->Type->Name == TagItemType::AttributeValue) {
            $html .= $tagItem->ItsExpression ? $content : htmlentities($content);
        }

        if (!$noChildren) {
            foreach ($children as &$childTag) {
                if (
                    $childTag->Type->Name === TagItemType::TextContent
                    || $childTag->Type->Name === TagItemType::Tag
                ) {
                    if ($noContent) {
                        $noContent = false;
                        if (!$selfClosing) {
                            $html .= '>';
                        }
                    }
                }
                $this->buildTag($childTag, $html);
            }
        }
        if ($tagItem->Type->Name == TagItemType::Attribute) {
            $html .= ($noChildren ? '' : '"');
        }

        if ($tagItem->Type->Name == TagItemType::Tag) {
            if ($selfClosing) {
                $html .= '/>';
            } else {
                if ($noContent) {
                    $html .= '>';
                }
                $html .= '</' . $content . '>';
                $this->extraLine = false;
            }
        } else if ($tagItem->Type->Name == TagItemType::Component) {
            if ($noContent) {
                $html .= '>';
            }
            //render child component, TODO: replace by script
            //$component = $this->templates[$tagItem->Content];
            //$this->buildInternal($component, $html);
            $html .= "_<[||{$content}||]>_";
            $html .= "</$replaceByTag>";
        }
        // if ($tagItem->Type->Name == TagItemType::Expression) {
        //     $html .= "_(||{$tagItem->Content}||)_";
        // }
    }

    function compileTemplate(ComponentInfo $componentInfo): PageTemplate
    {
        $template = new PageTemplate();
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
                            $skipInExpression = 1;
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
                                break;
                            }
                        case '}': {
                                if ($blocksCount > 0) {
                                    $blocksCount--;
                                } else { // end of expression
                                    $itsBlockExpression = false;
                                    $skipCount = 1;
                                    $saveContent = true;
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

        $template->RootTag->cleanParents();
        return $template;
    }

    function debug($any)
    {
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
