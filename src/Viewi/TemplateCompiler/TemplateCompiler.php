<?php

namespace Viewi\TemplateCompiler;

use Viewi\Builder\BuildItem;
use Viewi\Helpers;
use Viewi\JsTranspile\JsTranspiler;
use Viewi\Meta\Meta;
use Viewi\TemplateParser\TagItem;
use Viewi\TemplateParser\TagItemType;

class TemplateCompiler
{
    private string $code;
    /**
     * 
     * @var string[]
     */
    private array $plainItems = [];
    private string $template;
    private int $level = 0;
    private string $indentationPattern = '    ';
    private array $identations = [];
    private string $voidTagsString = 'area,base,br,col,embed,hr,img,input,link,meta,param,source,track,wbr';

    /** @var array<string,string> */
    private array $voidTags;
    private string $_CompileJsComponentName = '_component';

    public function __construct(private JsTranspiler $jsTranspiler)
    {
        $this->voidTags = array_flip(explode(',', $this->voidTagsString));
    }

    public function compile(TagItem $rootTag, BuildItem $buildItem, $templateKey = ''): string
    {
        $this->reset();
        $renderFunctionTemplate = $this->template ?? ($this->template = file_get_contents(Meta::renderFunctionPath()));
        $parts = explode("//#content", $renderFunctionTemplate, 2);
        $this->code .= $parts[0];
        $renderFunction = "Render{$buildItem->ComponentName}$templateKey";
        $this->code = str_replace('BaseComponent $', ($buildItem->Namespace ?? '') . '\\' . $buildItem->ComponentName . ' $', $this->code);
        $this->code = str_replace('RenderFunction', $renderFunction, $this->code);

        $this->buildTag($rootTag);

        if (count($this->plainItems) > 0) {
            $this->code .= PHP_EOL . $this->i() . '$_content .= ' . var_export(implode('', $this->plainItems), true) . ';';
            $this->plainItems = [];
        }
        $this->code .= $parts[1];
        return $this->code;
    }

    private function reset()
    {
        $this->code = '';
        $this->plainItems = [];
        $this->level = 1;
    }

    private function buildTag(TagItem &$tagItem)
    {
        $allChildren = $tagItem->getChildren();
        $attributes = [];
        /**
         * @var TagItem[]
         */
        $children = [];
        foreach ($allChildren as &$child) {
            if ($child->Type->Name === TagItemType::Attribute) {
                $attributes[] = $child;
            } else {
                $children[] = $child;
            }
        }
        $hasChildren = count($children) > 0;
        $hasAttributes = count($attributes) > 0;
        $root = $tagItem->Type->Name === TagItemType::Root;
        $tag = $tagItem->Type->Name === TagItemType::Tag;
        $expression = $tagItem->ItsExpression;
        $nested = $root || $tag;
        if ($nested) {
            $isVoid = $tag && isset($this->voidTags[$tagItem->Content]);
            if ($tag) {
                if ($expression) {
                    // dynamic tag or component
                    $this->buildExpression($tagItem);
                    $this->code .= PHP_EOL . $this->i() . '$_content .= ' . var_export(implode('', $this->plainItems), true) . ';';
                    $this->plainItems = [];
                    $this->code .= PHP_EOL . $this->i() . "if (\$_engine->isComponent({$tagItem->PhpExpression})) {";
                    $this->level++;
                    // component: TODO

                    $this->level--;
                    $this->code .= PHP_EOL . $this->i() . "} elseif ({$tagItem->PhpExpression}) {";
                    $this->level++;
                    // dynamic tag
                    $this->code .= PHP_EOL . $this->i() . '$_content .= ' . "'<' . htmlentities({$tagItem->PhpExpression} ?? '')" . ($hasAttributes ? '' : " . '>'") . ';';
                } else {
                    $this->plainItems[] = '<' . $tagItem->Content . ($hasAttributes ? '' : '>');
                }
                if ($hasAttributes) {
                    foreach ($attributes as &$attributeItem) {
                        $this->buildAttribute($attributeItem);
                    }
                    $this->plainItems[] = '>';
                }
            }
            if (!$isVoid) {
                if ($hasChildren) {
                    foreach ($children as &$childItem) {
                        if ($childItem->Type->Name === TagItemType::TextContent) {
                            if ($childItem->ItsExpression) {
                                $this->expression($childItem);
                            } else {
                                $this->plainItems[] = $childItem->Content;
                            }
                        } elseif ($childItem->Type->Name === TagItemType::Comment) {
                            $this->plainItems[] = '<!--' . htmlentities($childItem->Content) . '-->';
                        } else {
                            $this->buildTag($childItem);
                        }
                    }
                }
                if ($tag) {
                    if ($expression) {
                        $this->code .= PHP_EOL . $this->i() . '$_content .= ' . var_export(implode('', $this->plainItems), true) . ';';
                        $this->plainItems = [];
                        $this->code .= PHP_EOL . $this->i() . '$_content .= ' . "'</' . htmlentities({$tagItem->PhpExpression} ?? '') . '>'" . ';';
                        $this->level--;
                        $this->code .= PHP_EOL . $this->i() . "}";
                    } else {
                        $this->plainItems[] = '</' . $tagItem->Content . '>';
                    }
                }
            }
        }
    }

    private function buildAttribute(TagItem &$attributeItem)
    {
        $children = $attributeItem->getChildren();
        $hasChildren = count($children) > 0;
        if ($attributeItem->ItsExpression) {
            $this->buildExpression($attributeItem);
            $this->code .= PHP_EOL . $this->i() . '$_content .= ' . var_export(implode('', $this->plainItems), true) . ';';
            $this->plainItems = [];
            $this->code .= PHP_EOL . $this->i() . "if ({$attributeItem->PhpExpression} && {$attributeItem->PhpExpression}[0] !== '(') {";
            $this->level++;
            $this->code .= PHP_EOL . $this->i() . '$_content .= ' . "' ' . htmlentities({$attributeItem->PhpExpression} ?? '')" . ';';
            $this->plainItems[] = ($hasChildren ? '="' : '');
        } else {
            if (!$attributeItem->Content || $attributeItem->Content[0] === '(') {
                return; // event is handled on front-end only
            }
            $this->plainItems[] = ' ' . $attributeItem->Content . ($hasChildren ? '="' : '');
        }
        if ($hasChildren) {
            foreach ($children as $attributeValue) {
                if ($attributeValue->ItsExpression) {
                    $this->expression($attributeValue);
                } else {
                    $this->plainItems[] = htmlentities($attributeValue->Content);
                }
            }
            $this->plainItems[] = '"';
        }
        if ($attributeItem->ItsExpression) {
            $this->code .= PHP_EOL . $this->i() . '$_content .= ' . var_export(implode('', $this->plainItems), true) . ';';
            $this->plainItems = [];
            $this->level--;
            $this->code .= PHP_EOL . $this->i() . "}";
        }
    }

    private function buildExpression(TagItem &$tagItem)
    {
        $phpCode = $tagItem->Content;
        $jsOutput = $this->jsTranspiler->convert($phpCode, true, $this->_CompileJsComponentName);
        $tagItem->JsExpression = $jsOutput->__toString();
        $transforms = $jsOutput->getTransforms();
        // Helpers::debug($transforms);
        foreach ($transforms as $input => $replacement) {
            if ($input[0] === '$') {
                $phpCode = preg_replace('/\$\b' . substr($input, 1) . '\b/', '$' . $replacement, $phpCode);
            } else {
                $phpCode = preg_replace('/\b' . $input . '\b/', '$' . $replacement, $phpCode);
            }
        }
        $tagItem->PhpExpression = $phpCode;
    }

    private function expression(TagItem &$tagItem)
    {
        $this->buildExpression($tagItem);
        $this->code .= PHP_EOL . $this->i() . '$_content .= ' . var_export(implode('', $this->plainItems), true) . ';';
        $this->plainItems = [];
        $this->code .= PHP_EOL . $this->i() . '$_content .= ' . "htmlentities({$tagItem->PhpExpression} ?? '')" . ';';
    }

    private function i(): string
    {
        return ($this->identations[$this->level] ?? ($this->identations[$this->level] = str_repeat($this->indentationPattern, $this->level)));
    }
}
