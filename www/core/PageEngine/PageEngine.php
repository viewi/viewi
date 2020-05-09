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

    /** @var ComponentInfo[] */
    private array $components;

    /** @var mixed[] */
    private array $tokens;

    /** @var PageTemplate[] */
    private array $templates;

    /** @var string[string] */
    private array $reservedTags;

    /** @var string */
    private $reservedTagsString = 'html,body,base,head,link,meta,style,title,' .
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
        'polygon,polyline,rect,switch,symbol,text,textpath,tspan,use,view,template';

    public function __construct(string $sourcePath, string $buildPath, string $rootComponent)
    {
        $this->sourcePath = $sourcePath;
        $this->buildPath = $buildPath;
        $this->rootComponent = $rootComponent;
        $this->components = [];
        $this->tokens = [];
        $this->templates = [];
        $this->reservedTags = array_flip(explode(',', $this->reservedTagsString));
    }
    function startApp(): void
    {
        $this->Compile();
        if (!isset($this->components[$this->rootComponent])) {
            throw new Exception("Component {$this->rootComponent} is missing!");
        }
        $root = $this->components[$this->rootComponent];
        $rootApp = new ComponentRenderer();
        $rootApp->component = new $root->name();

        //$this->debug($rootApp);
        //$this->debug($this->rootComponent . ' ' . $this->path);
        //$this->debug($this->templates);
        //$this->debug($this->components);
        //$this->debug($this->tokens);
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
                $componentInfo->fullpath = $filename;
                if (isset($pages[$templatePath])) {
                    $componentInfo->templatePath = $templatePath;
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
                $componentInfo->name = $className;
                $componentInfo->tag = $className;

                if (!empty($className)) {
                    $this->components[$className] = $componentInfo;
                    $this->tokens[$className] = $tokens;
                }
            }
        }
        foreach ($this->components as $className => &$componentInfo) {
            $this->templates[$className] = $this->compileTemplate($componentInfo->templatePath);
        }
        $this->build($this->templates[$this->rootComponent]);
    }

    function compileTemplate(string $path)
    {
        $template = new PageTemplate();
        if (!empty($path)) {
            $template->Path = $path;
            $text = file_get_contents($path);
            $raw = str_split($text);

            /**
             * 1. <tagOpen
             * 2. </tagClose
             * 3. <TagAutoClose/>
             * 4. <ComponentTag>
             * 5. <UndefinedTag>
             * 6. $PHP expressions
             * 7. Events
             * 8. special attribute expressions
             * 9. attributes
             * 10. escaping \" \' {} $
             */

            $template->RootTag = new TagItem();
            $currentParent = &$template->RootTag;
            $currentType = new TagItemType(TagItemType::TextContent);
            $nextType = new TagItemType(TagItemType::TextContent);
            $skipCount = 0;
            $saveContent = false;
            $skipSaving = false;
            $hasNoChildren = false;
            $breakBySpaces = false;
            $lastBrealSymbol = '"';
            $itsAtomicExpression = false;
            $itsBlockExpression = false;
            $expressionInsideValue = false;
            $returnFromValueExpression = false;
            $returnOnValueEnd = false;
            $blocksCount = 0;
            $skipInExpression = 0;
            $previousType = false;
            $collectValue = false;
            $content = '';
            $value = '';
            $count = count($raw);
            for ($i = 0; $i < $count; $i++) {
                $char = $raw[$i];
                if (!$itsBlockExpression) {
                    switch ($char) {
                        case '<': { // open tag
                                $skipCount = 1;
                                $saveContent = true;
                                $nextType = new TagItemType(TagItemType::Tag);
                                break;
                            }
                        case '>': { // close tag
                                $skipCount = 1;
                                $saveContent = true;
                                $nextType = new TagItemType(TagItemType::TextContent);
                                $breakBySpaces = false;
                                break;
                            }
                        case '/': {
                                $skipCount = 1;
                                if (
                                    $currentType->Name == TagItemType::Tag
                                    || $currentType->Name == TagItemType::Attribute
                                ) { // if inside tag
                                    if (ctype_space($content) || empty($content)) {
                                        $skipSaving = true;
                                        //$this->debug(var_dump($content));
                                    }
                                    //$this->debug(var_dump($content));
                                    $hasNoChildren = true;
                                }
                                break;
                            }
                        case '{': {
                                $itsBlockExpression = true;
                                $skipCount = 1;
                                $skipInExpression = 1;
                                $saveContent = true;
                                $previousType = $currentType;
                                $nextType = new TagItemType(TagItemType::Expression);
                                break;
                            }
                        case '$': {
                                $itsAtomicExpression = true;
                                $skipInExpression = 1;
                                if ($currentType->Name == TagItemType::Attribute) {
                                    $expressionInsideValue = true;
                                }
                                $saveContent = true;
                                $previousType = $currentType;
                                $nextType = new TagItemType(TagItemType::Expression);
                                break;
                            }
                        case "'":
                        case '"': {
                                if ($nextType->Name == TagItemType::Attribute) {
                                    if ($breakBySpaces) {
                                        $breakBySpaces = false;
                                        $lastBrealSymbol = $char;
                                        $skipCount = 1;
                                    } else if ($lastBrealSymbol === $char) {
                                        $breakBySpaces = true;
                                        $skipCount = 1;
                                        if ($returnOnValueEnd) {
                                            $returnOnValueEnd = false;
                                            $returnFromValueExpression = true;
                                        }
                                    }
                                }
                                break;
                            }
                        case '=': { // attribute value
                                if ($nextType->Name == TagItemType::Attribute) {
                                    if (!$collectValue) {
                                        $collectValue = true;
                                        $skipCount = 1;
                                    }
                                } else {
                                    $collectValue = false;
                                }
                                break;
                            }
                        default: {
                                if (ctype_space($char)) { // space characters
                                    if ($breakBySpaces) {
                                        $skipCount = 1;
                                        $saveContent = true;
                                        $nextType = new TagItemType(TagItemType::Attribute);
                                    }
                                    if ($nextType->Name == TagItemType::Tag) {
                                        if (!empty($content)) {
                                            $breakBySpaces = true;
                                            $skipCount = 1;
                                            $saveContent = true;
                                            $nextType = new TagItemType(TagItemType::Attribute);
                                        }
                                    }
                                }
                                break;
                            }
                    }
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
                                        $nextType = $previousType;
                                        $previousType = false;
                                    }
                                    break;
                                }
                        }
                    }
                }
                if ($itsAtomicExpression) {
                    if ($skipInExpression > 0) {
                        $skipInExpression--;
                    } else {
                        if (!ctype_alnum($char)) {
                            $itsAtomicExpression = false;
                            $saveContent = true;
                            $nextType = $previousType;
                            if ($previousType->Name == TagItemType::Attribute) {
                                if ($lastBrealSymbol === $char) {
                                    $skipCount = 1;
                                    $returnFromValueExpression = true;
                                } else {
                                    $returnOnValueEnd = true;
                                }
                                // else {
                                //     $nextType = new TagItemType(TagItemType::TextContent);
                                // }
                            }
                            $previousType = false;
                            $expressionInsideValue = false;
                        }
                    }
                }
                if ($saveContent) {
                    $returnToParent = false;
                    if (!empty($content)) {
                        if (!$skipSaving) {
                            //add new child
                            $child = $currentParent->newChild();
                            $child->Type = $currentType;
                            if (
                                $currentType->Name === TagItemType::TextContent
                                || $currentType->Name === TagItemType::Expression
                            ) {
                                $child->Content = $content;
                            } else {
                                $child->Name = $content;
                                if ($currentType->Name === TagItemType::Tag) {
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
                                if (!empty($value)) {
                                    $child->Content = $value;
                                }
                            }

                            if (
                                ($currentType->Name == TagItemType::Tag && !$hasNoChildren)
                                || $expressionInsideValue
                            ) {
                                $currentParent = &$currentParent->currentChild();
                            }
                        } else {
                            if ($currentType->Name == TagItemType::Tag) {
                                $returnToParent = true;
                            }
                        }
                        //clear content
                        $collectValue = false;
                        $content = '';
                        $value = '';
                    } else if ($hasNoChildren) {
                        $returnToParent = true;
                    }
                    if ($returnToParent || $returnFromValueExpression) {
                        if (
                            $returnFromValueExpression
                            && $currentParent->Type->Name == TagItemType::Attribute
                        ) { // fix item type
                            $children = $currentParent->getChildren();
                            if (!empty($children)) {
                                if ($currentParent->Content) {
                                    $newChild = new TagItem();
                                    $newChild->Content = $currentParent->Content;
                                    $newChild->Type = new TagItemType(TagItemType::TextContent);
                                    $currentParent->prependChild($newChild);
                                    $currentParent->Content = null;
                                }
                                foreach ($children as &$child) {
                                    if ($child->Type->Name === TagItemType::Attribute) {
                                        $child->Content = $child->Name;
                                        $child->Name = null;
                                        $child->Type = new TagItemType(TagItemType::TextContent);
                                    }
                                }
                            }
                        }
                        $currentParent = &$currentParent->parent();
                        if ($currentParent === null) {
                            throw new Exception("Detected closing tag `$content` but open tag is missing!");
                        }
                        $returnFromValueExpression = false;
                    }
                    $currentType = $nextType;
                    $saveContent = false;
                    $skipSaving = false;
                    $hasNoChildren = false;
                }
                if ($skipCount > 0) {
                    $skipCount--;
                } else {
                    if ($collectValue) {
                        $value .= $char;
                    } else {
                        $content .= $char;
                    }
                }
            }
            $template->RootTag->cleanParents();
            //$this->debug($template);
            //$this->debug(htmlentities($html));
            //$this->debug(htmlentities($htmlUndefined));
        }
        return $template;
    }

    function build(PageTemplate &$pageTemplate): void
    {
        $html = '';
        $this->buildInternal($pageTemplate, $html);
        $this->debug(htmlentities($html));
        $this->debug($pageTemplate);
    }
    private function buildInternal(PageTemplate &$pageTemplate, string &$html): void
    {
        foreach ($pageTemplate->RootTag->getChildren() as &$tag) {
            $this->buildTag($tag, $html);
        }
    }
    function buildTag(TagItem &$tagItem, string &$html): void
    {
        $replaceByTag = 'div';
        /** @var TagItem[] */
        $children = $tagItem->getChildren();
        $noChildren = empty($children);
        $noContent = true;
        $itsTagOrComponent = $tagItem->Type->Name == TagItemType::Tag
            || $tagItem->Type->Name == TagItemType::Component;

        if ($tagItem->Type->Name == TagItemType::Tag) {
            $html .= '<' . $tagItem->Name;
        }

        if ($tagItem->Type->Name == TagItemType::Component) {
            $html .= "<$replaceByTag data-component=\"{$tagItem->Name}\"";
        }

        if ($tagItem->Type->Name == TagItemType::TextContent) {
            $html .= $tagItem->Content;
        }

        if ($tagItem->Type->Name == TagItemType::Attribute) {
            $html .= ' ' . $tagItem->Name . ($tagItem->Content
                ? '="' . htmlentities($tagItem->Content) . '"'
                : '');
        }

        if (!$noChildren) {
            foreach ($children as &$childTag) {
                if ($childTag->Type->Name == TagItemType::TextContent) {
                    if ($noContent) {
                        $noContent = false;
                        if ($itsTagOrComponent) {
                            $html .= '>';
                        }
                    }
                }
                $this->buildTag($childTag, $html);
            }
        }

        if ($tagItem->Type->Name == TagItemType::Tag) {
            if ($noContent) {
                $html .= '/>';
            } else {
                $html .= '</' . $tagItem->Name . '>';
            }
        } else if ($tagItem->Type->Name == TagItemType::Component) {
            if ($noContent) {
                $html .= '>';
            }
            //render child component, TODO: replace by script
            //$component = $this->templates[$tagItem->Name];
            //$this->buildInternal($component, $html);
            $html .= "_<[||{$tagItem->Name}||]>_";
            $html .= "</$replaceByTag>";
        }

        if ($tagItem->Type->Name == TagItemType::Expression) {
            $html .= "_(||{$tagItem->Content}||)_";
        }
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
