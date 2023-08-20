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
    private array $slotsIndex = [];
    private string $voidTagsString = 'area,base,br,col,embed,hr,img,input,link,meta,param,source,track,wbr';

    /** @var array<string,string> */
    private array $voidTags;
    private string $_CompileJsComponentName = '_component';
    private BuildItem $buildItem;
    private ?string $parentComponentName = null;
    /**
     * 
     * @var RenderItem[]
     */
    private array $slots;

    public function __construct(private JsTranspiler $jsTranspiler)
    {
        $this->voidTags = array_flip(explode(',', $this->voidTagsString));
    }

    public function compile(
        TagItem $rootTag, // template
        BuildItem $buildItem, // scope
        $templateKey = '', // for slots
        ?string $parentComponentName = null // for slots
    ): RenderItem {
        $this->reset();
        $this->buildItem = $buildItem;
        $this->parentComponentName = $parentComponentName;
        $renderFunctionTemplate = $this->template
            ?? ($this->template = str_replace('<?php', '', file_get_contents(Meta::renderFunctionPath())));
        $parts = explode("//#content", $renderFunctionTemplate, 2);
        $funcBegin = $parts[0];
        $renderFunction = "Render{$buildItem->ComponentName}$templateKey";
        if (!isset($this->slotsIndex[$renderFunction])) {
            $this->slotsIndex[$renderFunction] = -1;
        }
        $this->slotsIndex[$renderFunction]++;
        if ($this->slotsIndex[$renderFunction] > 0) {
            $renderFunction .= $this->slotsIndex[$renderFunction];
        }
        $funcBegin = str_replace('BaseComponent $', ($buildItem->Namespace ?? '') . '\\' . $buildItem->ComponentName . ' $', $funcBegin);
        $funcBegin = str_replace('RenderFunction', $renderFunction, $funcBegin);

        $this->buildTag($rootTag);

        if (count($this->plainItems) > 0) {
            $this->code .= PHP_EOL . $this->i() . '$_content .= ' . var_export(implode('', $this->plainItems), true) . ';';
            $this->plainItems = [];
        }
        return new RenderItem($funcBegin . $this->code . $parts[1], !$this->code, $renderFunction, $this->slots);
    }

    private function reset()
    {
        $this->code = '';
        $this->plainItems = [];
        $this->level = 1;
        $this->slots = [];
    }

    private function preserve(): array
    {
        return [
            'code' => $this->code,
            'plainItems' => $this->plainItems,
            'level' => $this->level,
            'slots' => $this->slots,
        ];
    }

    private function restore(array $state)
    {
        $this->code = $state['code'];
        $this->plainItems = $state['plainItems'];
        $this->level = $state['level'];
        $this->slots = $state['slots'];
    }

    private function buildTag(TagItem &$tagItem)
    {
        $allChildren = $tagItem->getChildren();
        /**
         * @var TagItem[]
         */
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
        $slot = false;
        $slotContent = false;
        if ($tag) {
            if ($tagItem->Content === 'slot') {
                $slot = true;
                $tag = false;
            } elseif ($tagItem->Content === 'slotContent') {
                $slotContent = true;
                $tag = false;
            }
        }
        $component = $tagItem->Type->Name === TagItemType::Component;
        $expression = $tagItem->ItsExpression;
        $nested = $root || $tag || $component || $slot || $slotContent;
        if ($nested) {
            $isVoid = $tag && isset($this->voidTags[$tagItem->Content]);

            if ($tag) {
                if ($expression) {
                    // dynamic tag or component
                    $this->buildExpression($tagItem);
                    if (count($this->plainItems) > 0) {
                        $this->code .= PHP_EOL . $this->i() . '$_content .= ' . var_export(implode('', $this->plainItems), true) . ';';
                        $this->plainItems = [];
                    }
                    $this->code .= PHP_EOL . $this->i() . "if (\$_engine->isComponent({$tagItem->PhpExpression})) {";
                    $this->level++;
                    $component = true;
                }
            }
            if ($slotContent) {
                if (count($this->plainItems) > 0) {
                    $this->code .= PHP_EOL . $this->i() . '$_content .= ' . var_export(implode('', $this->plainItems), true) . ';';
                    $this->plainItems = [];
                }
                $slotContentName = '\'default\'';
                $slotContentRawName = 'default';
                $slotAttribute = $this->extractAttribute('name', $attributes);
                if ($slotAttribute !== null) {
                    $nameValues = $slotAttribute->getChildren();
                    if (count($nameValues) > 0) {
                        $nameValue = $nameValues[0];
                        if ($nameValue->ItsExpression) {
                            $this->buildExpression($nameValue);
                            $slotContentName = $nameValue->PhpExpression;
                            $slotContentRawName = 'expr';
                        } else {
                            $slotContentName = var_export($nameValue->Content, true);
                            $slotContentRawName = $nameValue->Content;
                        }
                    }
                }
                // build slot content
                $lastState = $this->preserve();
                $slotRoot = new TagItem();
                $slotRoot->Type = new TagItemType(TagItemType::Root);
                $slotRoot->setChildren($children);
                $slotFunction = $this->compile(
                    $slotRoot,
                    $this->buildItem,
                    '_' . $this->parentComponentName . '_' . $slotContentRawName,
                    $this->parentComponentName
                );
                $this->restore($lastState);
                $this->slots[] = [$slotContentRawName, $slotFunction];
                // Helpers::debug($slotFunction);
                return;
            }
            // == COMPONENT ==
            if ($component) {
                if (count($this->plainItems)) {
                    $this->code .= PHP_EOL . $this->i() . '$_content .= ' . var_export(implode('', $this->plainItems), true) . ';';
                    $this->plainItems = [];
                }
                $componentName = $tagItem->PhpExpression ?? var_export($tagItem->Content, true);
                $rawComponentName = $tagItem->ItsExpression ? 'X' : $tagItem->Content;
                // pass props
                $inputArguments = [];
                if ($hasAttributes) {
                    $comma = '';
                    $this->level++;
                    foreach ($attributes as &$attributeItem) {
                        if ($attributeItem->ItsExpression) {
                            $this->buildExpression($attributeItem);
                        }
                        $values = $attributeItem->getChildren();
                        $hasValues = count($values) > 0;
                        $combinedValue = $hasValues ? '' : 'true';
                        $concat = '';
                        foreach ($values as &$attributeValue) {
                            if ($attributeValue->ItsExpression) {
                                $this->buildExpression($attributeValue);
                                $combinedValue .= $concat . "({$attributeValue->PhpExpression})";
                            } else {
                                if (
                                    ctype_digit($attributeValue->Content)
                                    || $attributeValue->Content === 'false'
                                    || $attributeValue->Content === 'true'
                                ) {
                                    $combinedValue .= $concat . $attributeValue->Content;
                                } else {
                                    $combinedValue .= $concat . var_export($attributeValue->Content, true);
                                }
                            }
                            $concat = ' . ';
                        }
                        $name = $attributeItem->PhpExpression ?? var_export($attributeItem->Content, true);
                        $inputArguments[] = "{$comma}$name => $combinedValue";
                        $comma = ',' . PHP_EOL . $this->i();
                    }
                    $this->level--;
                }
                // build slots
                $lastState = $this->preserve();
                $slotRoot = new TagItem();
                $slotRoot->Type = new TagItemType(TagItemType::Root);
                $slotRoot->setChildren($children);
                $slotContentName = '_' . $rawComponentName . '_default';
                $slotFunction = $this->compile($slotRoot, $this->buildItem, $slotContentName, $rawComponentName);
                $this->restore($lastState);
                $passThroughSlots = [];
                $comma = '';
                $this->level++;
                $trackMap = [];
                if (!$slotFunction->empty) {
                    $this->slots[] = ['default', $slotFunction];
                    $defaultRenderName = var_export($slotFunction->renderName, true);
                    $passThroughSlots[] = "{$comma}'default' => $defaultRenderName";
                    $trackMap = ['default' => true];
                    $comma = ',' . PHP_EOL . $this->i();
                }
                foreach ($slotFunction->slots as $childSlot) {
                    $this->slots[] = $childSlot;
                    if (!isset($trackMap[$childSlot[0]])) {
                        $slotKey = var_export($childSlot[0], true);
                        $renderName = var_export($childSlot[1]->renderName, true);
                        $passThroughSlots[] = "{$comma}$slotKey => $renderName";
                        $comma = ',' . PHP_EOL . $this->i();
                    }
                }
                $this->level--;
                // Helpers::debug($slotFunction);
                $slotFunction->slots = [];
                // pass slots
                $slotsMap = $passThroughSlots
                    ? "'component' => \$_component, 'map' => [" .
                    PHP_EOL . $this->i() . $this->indentationPattern . implode('', $passThroughSlots) . PHP_EOL . $this->i() .
                    ']'
                    : '';
                $props = $inputArguments
                    ? PHP_EOL . $this->i() . $this->indentationPattern . implode('', $inputArguments) . PHP_EOL . $this->i()
                    : '';
                $this->code .= PHP_EOL . $this->i() . "\$_content .= \$_engine->renderComponent($componentName, [$props], [$slotsMap], \$_slots);";
                if (!$expression) {
                    return;
                }
            }
            // == END COMPONENT ==

            if ($tag) {
                if ($expression) {
                    // dynamic tag or component
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
                if ($slot) {
                    if (count($this->plainItems) > 0) {
                        $this->code .= PHP_EOL . $this->i() . '$_content .= ' . var_export(implode('', $this->plainItems), true) . ';';
                        $this->plainItems = [];
                    }
                    $slotContentName = '\'default\'';
                    $slotAttribute = $this->extractAttribute('name', $attributes);
                    if ($slotAttribute !== null) {
                        $nameValues = $slotAttribute->getChildren();
                        if (count($nameValues) > 0) {
                            $nameValue = $nameValues[0];
                            if ($nameValue->ItsExpression) {
                                $this->buildExpression($nameValue);
                                $slotContentName = $nameValue->PhpExpression;
                            } else {
                                $slotContentName = var_export($nameValue->Content, true);
                            }
                        }
                    }
                    $this->code .= PHP_EOL . $this->i() . "if (isset(\$_slots['map'][$slotContentName])) {";
                    $this->level++;
                    $this->code .= PHP_EOL . $this->i() . "\$_content .= \$_engine->renderSlot(\$_slots['component'], \$_slots['map'][$slotContentName], \$_parentSlots);";
                    $this->level--;
                    $this->code .= PHP_EOL . $this->i() . "} else {";
                    $this->level++;
                }
                if ($hasChildren) {
                    foreach ($children as &$childItem) {
                        if ($childItem->Type->Name === TagItemType::TextContent) {
                            if ($childItem->ItsExpression) {
                                $this->appendExpression($childItem);
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
                if ($slot) {
                    if (count($this->plainItems) > 0) {
                        $this->code .= PHP_EOL . $this->i() . '$_content .= ' . var_export(implode('', $this->plainItems), true) . ';';
                        $this->plainItems = [];
                    }
                    $this->level--;
                    $this->code .= PHP_EOL . $this->i() . "}";
                }
                if ($tag) {
                    if ($expression) {
                        if (count($this->plainItems) > 0) {
                            $this->code .= PHP_EOL . $this->i() . '$_content .= ' . var_export(implode('', $this->plainItems), true) . ';';
                            $this->plainItems = [];
                        }
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

    /**
     * 
     * @param string $name 
     * @param TagItem[] $attributes 
     * @return null | TagItem
     */
    private function &extractAttribute(string $name, array &$attributes)
    {
        foreach ($attributes as &$attribute) {
            if ($attribute->Content === $name) {
                return $attribute;
            }
        }
        return null;
    }

    private function buildAttribute(TagItem &$attributeItem)
    {
        $children = $attributeItem->getChildren();
        $childrenCount = count($children);
        if ($attributeItem->ItsExpression) {
            $this->buildExpression($attributeItem);
            if (count($this->plainItems) > 0) {
                $this->code .= PHP_EOL . $this->i() . '$_content .= ' . var_export(implode('', $this->plainItems), true) . ';';
                $this->plainItems = [];
            }
            $this->code .= PHP_EOL . $this->i() . "if ({$attributeItem->PhpExpression} && {$attributeItem->PhpExpression}[0] !== '(') {";
            $this->level++;
        } else {
            if (!$attributeItem->Content || $attributeItem->Content[0] === '(') {
                return; // event is handled on front-end only
            }
        }
        if ($childrenCount === 1 && $children[0]->ItsExpression) {
            $attributeValue = &$children[0];
            $this->buildExpression($attributeValue);
            if (count($this->plainItems) > 0) {
                $this->code .= PHP_EOL . $this->i() . '$_content .= ' . var_export(implode('', $this->plainItems), true) . ';';
                $this->plainItems = [];
            }
            $this->code .= PHP_EOL . $this->i() . "\$tempVal = {$attributeValue->PhpExpression};";
            $this->code .= PHP_EOL . $this->i() . 'if ($tempVal !== null) {';
            $this->level++;
            if ($attributeItem->ItsExpression) {
                $this->code .= PHP_EOL . $this->i() . "\$_content .= ' ' . {$attributeItem->PhpExpression}";
                $this->code .= ' . \'="\' . htmlentities($tempVal ?? \'\') . \'"\';';
            } else {
                $this->code .= PHP_EOL . $this->i() . '$_content .= ' . var_export(' ' . $attributeItem->Content . '="', true);
                $this->code .= ' . htmlentities($tempVal ?? \'\') . \'"\';';
            }
            $this->level--;
            $this->code .= PHP_EOL . $this->i() . "}";
        } elseif ($childrenCount > 0) {
            if ($attributeItem->ItsExpression) {
                $this->code .= PHP_EOL . $this->i() . '$_content .= ' . "' ' . htmlentities({$attributeItem->PhpExpression} ?? '')" . ';';
                $this->plainItems[] =  '="';
            } else {
                $this->plainItems[] = ' ' . $attributeItem->Content . '="';
            }
            foreach ($children as &$attributeValue) {
                if ($attributeValue->ItsExpression) {
                    $this->appendExpression($attributeValue);
                } else {
                    $this->plainItems[] = htmlentities($attributeValue->Content);
                }
            }
            $this->plainItems[] = '"';
        }
        if ($attributeItem->ItsExpression) {
            if (count($this->plainItems) > 0) {
                $this->code .= PHP_EOL . $this->i() . '$_content .= ' . var_export(implode('', $this->plainItems), true) . ';';
                $this->plainItems = [];
            }
            $this->level--;
            $this->code .= PHP_EOL . $this->i() . "}";
        }
    }

    private function buildExpression(TagItem &$tagItem)
    {
        if ($tagItem->PhpExpression) {
            return;
        }
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

    private function appendExpression(TagItem &$tagItem)
    {
        $this->buildExpression($tagItem);
        if (count($this->plainItems) > 0) {
            $this->code .= PHP_EOL . $this->i() . '$_content .= ' . var_export(implode('', $this->plainItems), true) . ';';
            $this->plainItems = [];
        }
        $this->code .= PHP_EOL . $this->i() . '$_content .= ' . "htmlentities({$tagItem->PhpExpression} ?? '')" . ';';
    }

    private function i(): string
    {
        return ($this->identations[$this->level]
            ?? ($this->identations[$this->level] = str_repeat($this->indentationPattern, $this->level)));
    }
}
