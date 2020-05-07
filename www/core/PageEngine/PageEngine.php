<?php

require 'TagItemType.php';
require 'BaseComponent.php';
require 'ComponentInfo.php';
require 'ComponentRenderer.php';
require 'PageTemplate.php';
require 'TagItem.php';

class PageEngine
{
    /** @var string */
    private $path;

    /** @var string */
    private $rootComponent;

    /** @var ComponentInfo[] */
    private $components;

    /** @var mixed[] */
    private $tokens;

    /** @var PageTemplate[] */
    private $templates;

    /** @var string[string] */
    private $reservedTags;

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

    public function __construct(string $path, string $rootComponent)
    {
        $this->path = $path;
        $this->rootComponent = $rootComponent;
        $this->components = [];
        $this->tokens = [];
        $this->templates = [];
        $this->reservedTags = array_flip(explode(',', $this->reservedTagsString));
    }
    function StartApp(): void
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
        $pages = $this->getDirContents($this->path);
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
            $this->templates[$className] = $this->CompileTemplate($componentInfo->templatePath);
        }
    }

    function CompileTemplate(string $path)
    {
        $template = new PageTemplate();
        if (!empty($path)) {
            $template->path = $path;
            $this->debug($path);
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
             * 10. escaping and quotes skipping
             */

            $rootTag = new TagItem();
            $currentParent = &$rootTag->newChild();
            $currentType = new TagItemType(TagItemType::TextContent);
            $nextType = new TagItemType(TagItemType::TextContent);
            $skipCount = 0;
            $saveContent = false;
            $skipSaving = false;
            $hasNoChildren = false;

            $content = '';
            $count = count($raw);
            for ($i = 0; $i < $count; $i++) {
                $char = $raw[$i];
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
                            break;
                        }
                    case '/': {
                            $skipCount = 1;
                            if ($currentType->Type == TagItemType::Tag) {
                                if (empty($content)) {
                                    $skipSaving = true;
                                } else {
                                    $hasNoChildren = true;
                                }
                            }
                            break;
                        }
                    case '{':
                    case '$': { // php expression

                            break;
                        }
                }
                if ($saveContent) {
                    if (!empty($content)) {
                        if (!$skipSaving) {
                            //add new child
                            $child = $currentParent->newChild();
                            $child->Content = $content;
                            $child->type = $currentType;
                            if ($currentType->Type == TagItemType::Tag && !$hasNoChildren) {
                                $currentParent = &$currentParent->currentChild();
                            }
                        } else {
                            if ($currentType->Type == TagItemType::Tag) {
                                $currentParent = &$currentParent->parent();
                            }
                        }
                        //clear content
                        $content = '';
                    }
                    $currentType = $nextType;
                    $saveContent = false;
                    $skipSaving = false;
                    $hasNoChildren = false;
                }
                if ($skipCount > 0) {
                    $skipCount--;
                } else {
                    $content .= $char;
                }
            }
            $rootTag->cleanParents();
            $this->debug($rootTag);
            //$this->debug(htmlentities($html));
            //$this->debug(htmlentities($htmlUndefined));
        }
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
