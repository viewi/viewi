<?php

namespace Viewi\TemplateParser;

use Exception;

class TemplateParser
{
    private string $voidTagsString = 'area,base,br,col,embed,hr,img,input,link,meta,' .
        'param,source,track,wbr';

    /** @var array<string,string> */
    private array $voidTags;

    private string $reservedTagsString = 'html,body,base,head,link,meta,style,title,' .
        'address,article,aside,footer,header,h1,h2,h3,h4,h5,h6,hgroup,nav,section,' .
        'div,dd,dl,dt,figcaption,figure,picture,hr,img,li,linearGradient,main,ol,p,pre,ul,' .
        'a,b,abbr,bdi,bdo,br,cite,code,data,dfn,em,i,kbd,mark,q,rp,rt,rtc,ruby,' .
        's,samp,small,span,strong,sub,sup,time,u,var,wbr,area,audio,map,track,video,' .
        'embed,object,param,source,canvas,script,noscript,del,ins,' .
        'caption,col,colgroup,table,thead,tbody,td,th,tr,' .
        'button,datalist,fieldset,form,input,label,legend,meter,optgroup,option,' .
        'output,progress,select,stop,textarea,' .
        'polygon,polyline,details,dialog,menu,menuitem,summary,' .
        'content,element,shadow,template,blockquote,iframe,tfoot,' .
        'svg,animate,circle,clippath,cursor,defs,desc,ellipse,filter,font-face,' .
        'foreignObject,g,glyph,image,line,marker,mask,missing-glyph,path,pattern,' .
        'rect,switch,symbol,text,textpath,tspan,use,view,template,slot,slotContent';

    /** @var array<string, string> */
    private array $reservedTags;
    private array $components = [];

    public function __construct()
    {
        $this->voidTags = array_flip(explode(',', $this->voidTagsString));
        $this->reservedTags = array_flip(explode(',', $this->reservedTagsString));
    }

    public function setAvaliableComponents(array $components): void
    {
        $this->components = $components;
    }

    public function parse(string $content): TagItem
    {
        $template = new TagItem();
        $template->Type = new TagItemType(TagItemType::Root);
        $raw = str_split($content);
        $currentParent = &$template;
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
                                        || $raw[$i + 1] === '{' // dynamic tag
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
                            if ($escapeNextChar || $currentType->Name === TagItemType::Comment) {
                                $escapeNextChar = false;
                                break;
                            }
                            // allow inline style and scripts
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
                            if ($escapeNextChar || $currentType->Name === TagItemType::Comment) {
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
                                if (!ctype_alnum($char) && $char !== '_') {
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
                            && !isset($this->reservedTags[$content])
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
                        throw new Exception("Can't get child node.");
                        break;
                    }
                }
                if ($goUp && !$goDown) {
                    if ($currentParent->parent()) {
                        $currentParent = &$currentParent->parent();
                    } else {
                        throw new Exception("Can't get parent node.");
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
                    && !isset($this->reservedTags[$content])
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
}
