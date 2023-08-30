<?php

namespace Viewi\TemplateCompiler;

use Exception;
use Viewi\Builder\BuildItem;
use Viewi\Helpers;
use Viewi\JsTranspile\ExportItem;
use Viewi\JsTranspile\JsTranspiler;
use Viewi\Meta\Meta;
use Viewi\TemplateParser\DataExpression;
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
    private int $forIterationKey = 0;
    private array $localScope = [];
    private array $usedFunctions = [];
    private array $inlineExpressions = [];

    public function __construct(private JsTranspiler $jsTranspiler)
    {
        $this->voidTags = array_flip(explode(',', $this->voidTagsString));
    }

    public function compile(
        TagItem $rootTag, // template
        BuildItem $buildItem, // scope
        $templateKey = '', // for slots
        ?string $parentComponentName = null, // for slots
        bool $resetAll = true
    ): RenderItem {
        $this->reset($resetAll);
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

        $this->flushBuffer();
        if (count($this->localScope) > 0) {
            $scopeVariables = '[';
            $this->level++;
            $comma = PHP_EOL . $this->i();
            foreach ($this->localScope as $varName => $_) {
                $scopeVariables .= "{$comma}'$varName' => \$$varName";
                $comma = ',' . PHP_EOL . $this->i();
            }
            $this->level--;
            $scopeVariables .= PHP_EOL . $this->i() . ']';
            $funcBegin .= $scopeVariables . ' = $_scope;';
        }
        return new RenderItem(
            $funcBegin . $this->code . $parts[1],
            !$this->code,
            $renderFunction,
            $this->slots,
            $this->usedFunctions,
            $this->inlineExpressions
        );
    }

    private function reset(bool $all = true)
    {
        $this->code = '';
        $this->plainItems = [];
        $this->level = 1;
        $this->slots = [];
        if ($all) {
            $this->localScope = [];
            $this->forIterationKey = 0;
            $this->usedFunctions = [];
            $this->inlineExpressions = [];
        }
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

    private function flushBuffer()
    {
        if (count($this->plainItems) > 0) {
            $this->code .= PHP_EOL . $this->i() . '$_content .= ' . var_export(implode('', $this->plainItems), true) . ';';
            $this->plainItems = [];
        }
    }

    function getNextIterationKey(): string
    {
        ++$this->forIterationKey;
        return "_key{$this->forIterationKey}";
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
            if (!$child->Used) {
                if ($child->Type->Name === TagItemType::Attribute) {
                    if (
                        $child->Content === 'if'
                        || $child->Content === 'else-if'
                        || $child->Content === 'else'
                    ) {
                        // == IF ==
                        $child->Used = true;
                        $itsIf = $child->Content === 'if';
                        $itsElseIf = $child->Content === 'else-if';
                        $itsElse = $child->Content === 'else';
                        // insert If
                        $ifTagValue = null;
                        if (!$itsElse) {
                            $ifValues = $child->getChildren();
                            if (count($ifValues) === 0) {
                                throw new Exception("if/else-if/else directive requires expression.");
                            }
                            $expression = '';
                            foreach ($ifValues as &$ifvalue) {
                                $expression .= $ifvalue->Content;
                            }
                            $ifTagValue = &$ifValues[0];
                            $ifTagValue->ItsExpression = true;
                            $ifTagValue->Content = $expression;
                            $child->setChildren([$ifTagValue]);
                            $this->buildExpression($ifTagValue);
                        }
                        if ($itsElseIf || $itsElse) {
                            // validate previous tag for if/else-if
                            $plainCount = count($this->plainItems);
                            if (
                                $plainCount > 1
                                || ($plainCount > 0 && trim(implode('', $this->plainItems)))
                                || $this->code[strlen($this->code) - 1] !== '}'
                            ) {
                                throw new Exception("else-if/else directive requires previous if/else-if directive.");
                            }
                            $this->plainItems = [];
                        } else {
                            $this->flushBuffer();
                        }
                        if (!$itsElse) {
                            $this->code .= ($itsElseIf ? ' else' : PHP_EOL . $this->i()) . "if ({$ifTagValue->PhpExpression}) {";
                        } else {
                            $this->code .= " else {";
                        }
                        $this->level++;
                        $this->buildTag($tagItem);
                        $this->flushBuffer();
                        $this->level--;
                        $this->code .= PHP_EOL . $this->i() . "}";
                        return;
                        // == IF END ==
                    } elseif ($child->Content === 'foreach') {
                        // == FOREACH ==                        
                        $child->Used = true;
                        $foreachValues = $child->getChildren();
                        if (count($foreachValues) === 0) {
                            throw new Exception("foreach directive requires expression.");
                        }
                        $expression = '';
                        foreach ($foreachValues as &$ifvalue) {
                            $expression .= $ifvalue->Content;
                        }
                        $foreachTagValue = &$foreachValues[0];
                        $foreachTagValue->ItsExpression = true;
                        $foreachTagValue->Content = $expression;
                        $child->setChildren([$foreachTagValue]);
                        $foreachParts = explode(' as ', $expression);
                        $phpCode = $foreachParts[0];
                        $foreachTagValue->DataExpression = new DataExpression();
                        $jsOutput = $this->jsTranspiler->convert($foreachParts[0], true, $this->_CompileJsComponentName, $this->localScope);
                        $foreachTagValue->DataExpression->ForData = $jsOutput->__toString();

                        $transforms = $jsOutput->getTransforms();
                        foreach ($transforms as $input => $replacement) {
                            if ($input[0] === '$') {
                                $phpCode = preg_replace('/\$\b' . substr($input, 1) . '\b/', '$' . $replacement, $phpCode);
                            } else {
                                $phpCode = preg_replace('/\b' . $input . '\b/', '$' . $replacement, $phpCode);
                            }
                        }
                        $foreachTagValue->PhpExpression = $phpCode;
                        $foreachLoop = $foreachParts[1];
                        $foreachAsParts = explode('=>', $foreachLoop);
                        $prevScope = $this->localScope;
                        if (count($foreachAsParts) > 1) {
                            $jsOutput = $this->jsTranspiler->convert($foreachAsParts[0], true, null, $this->localScope);
                            $foreachTagValue->DataExpression->ForKey = $jsOutput->__toString();
                            $argument = trim($foreachAsParts[0]);
                            if ($argument[0] === '$') {
                                $argument = substr($argument, 1);
                            }
                            $this->localScope[$argument] = true;
                            $jsOutput = $this->jsTranspiler->convert($foreachAsParts[1], true, null, $this->localScope);
                            $foreachTagValue->DataExpression->ForItem = $jsOutput->__toString();
                            $argument = trim($foreachAsParts[1]);
                            if ($argument[0] === '$') {
                                $argument = substr($argument, 1);
                            }
                            $this->localScope[$argument] = true;
                        } else {
                            $foreachTagValue->DataExpression->ForKey = $this->getNextIterationKey();
                            $jsOutput = $this->jsTranspiler->convert($foreachAsParts[0], true, null, $this->localScope);
                            $foreachTagValue->DataExpression->ForItem = $jsOutput->__toString();
                            $argument = trim($foreachAsParts[0]);
                            if ($argument[0] === '$') {
                                $argument = substr($argument, 1);
                            }
                            $this->localScope[$argument] = true;
                        }
                        $this->flushBuffer();
                        $this->code .= PHP_EOL . $this->i() . "foreach ({$foreachTagValue->PhpExpression} as $foreachLoop) {";
                        $this->level++;
                        $this->buildTag($tagItem);
                        $this->flushBuffer();
                        $this->level--;
                        $this->code .= PHP_EOL . $this->i() . "}";
                        $this->localScope = $prevScope;
                        return;
                        // == FOREACH END ==
                    }
                    $attributes[] = $child;
                } else {
                    $children[] = $child;
                }
            }
        }
        $hasChildren = count($children) > 0;
        $hasAttributes = count($attributes) > 0;

        $root = $tagItem->Type->Name === TagItemType::Root;
        $tag = $tagItem->Type->Name === TagItemType::Tag;
        $slot = false;
        $slotContent = false;
        $templateTag = false;
        if ($tag) {
            if ($tagItem->Content === 'slot') {
                $slot = true;
                $tag = false;
            } elseif ($tagItem->Content === 'slotContent') {
                $slotContent = true;
                $tag = false;
            } elseif ($tagItem->Content === 'template') {
                $templateTag = true;
                $tag = false;
            }
        }
        $component = $tagItem->Type->Name === TagItemType::Component;
        $expression = $tagItem->ItsExpression;
        $nested = $root || $tag || $component || $slot || $slotContent || $templateTag;

        if ($nested) {
            $isVoid = $tag && isset($this->voidTags[$tagItem->Content]);

            if ($tag) {
                if ($expression) {
                    // dynamic tag or component
                    $this->buildExpression($tagItem);
                    $this->flushBuffer();
                    $this->code .= PHP_EOL . $this->i() . "if (\$_engine->isComponent({$tagItem->PhpExpression})) {";
                    $this->level++;
                    $component = true;
                }
            }
            if ($slotContent) {
                $this->flushBuffer();
                $slotContentName = '\'default\'';
                $slotContentRawName = 'default';
                $slotAttribute = $this->extractAttribute('name', $attributes);
                if ($slotAttribute !== null) {
                    $ifValue = $slotAttribute->getChildren();
                    if (count($ifValue) > 0) {
                        $nameValue = $ifValue[0];
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
                    $this->parentComponentName,
                    false
                );
                $this->restore($lastState);
                $this->slots[] = [$slotContentRawName, $slotFunction];
                // Helpers::debug($slotFunction);
                return;
            }
            // == COMPONENT ==
            if ($component) {
                $this->flushBuffer();
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
                $slotFunction = $this->compile($slotRoot, $this->buildItem, $slotContentName, $rawComponentName, false);
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
                $props = $inputArguments
                    ? PHP_EOL . $this->i() . $this->indentationPattern . implode('', $inputArguments) . PHP_EOL . $this->i()
                    : '';

                $scopeVariables = [];
                $comma = '';
                $this->level++;
                foreach ($this->localScope as $varName => $_) {
                    $scopeVariables[] = "{$comma}'$varName' => \$$varName";
                    $comma = ',' . PHP_EOL . $this->i();
                }
                $this->level--;
                $scope = $scopeVariables
                    ? '$_scope + [' . PHP_EOL . $this->i() . $this->indentationPattern . implode('', $scopeVariables) . PHP_EOL . $this->i() . ']'
                    : '$_scope';
                // pass slots
                $slotsMap = $passThroughSlots
                    ? "'component' => \$_component, 'parent' => \$_slots, 'map' => [" .
                    PHP_EOL . $this->i() . $this->indentationPattern . implode('', $passThroughSlots) . PHP_EOL . $this->i() .
                    ']'
                    : '';

                $this->code .= PHP_EOL . $this->i() . "\$_content .= \$_engine->renderComponent($componentName, [$props], [$slotsMap], $scope);";
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
                    $this->flushBuffer();
                    $slotContentName = '\'default\'';
                    $slotAttribute = $this->extractAttribute('name', $attributes);
                    if ($slotAttribute !== null) {
                        $ifValue = $slotAttribute->getChildren();
                        if (count($ifValue) > 0) {
                            $nameValue = $ifValue[0];
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
                    $this->code .= PHP_EOL . $this->i() . "\$_content .= \$_engine->renderSlot(\$_slots['component'], \$_scope, \$_slots['map'][$slotContentName], \$_slots['parent']);";
                    $this->level--;
                    $this->code .= PHP_EOL . $this->i() . ($hasChildren ? '} else {' : '}');
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
                if ($slot && $hasChildren) {
                    $this->flushBuffer();
                    $this->level--;
                    $this->code .= PHP_EOL . $this->i() . "}";
                }
                if ($tag) {
                    if ($expression) {
                        $this->flushBuffer();
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
            $this->flushBuffer();
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
            $this->flushBuffer();
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
            $this->flushBuffer();
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
        $jsOutput = $this->jsTranspiler->convert($phpCode, true, $this->_CompileJsComponentName, $this->localScope);
        $tagItem->JsExpression = $jsOutput->__toString();
        $transforms = $jsOutput->getTransforms();
        foreach ($transforms as $input => $replacement) {
            if ($input[0] === '$') {
                $propName = substr($input, 1);
                if (
                    isset($this->buildItem->publicNodes[$propName])
                    && $this->buildItem->publicNodes[$propName] === ExportItem::Property
                ) {
                    $phpCode = preg_replace('/\$\b' . substr($input, 1) . '\b/', '$' . $replacement, $phpCode);
                } else {
                    throw new Exception("Access to undeclared public property $propName in {$this->buildItem->TemplatePath}.");
                }
            } else {
                if (
                    isset($this->buildItem->publicNodes[$input])
                    && $this->buildItem->publicNodes[$input] === ExportItem::Method
                ) {
                    $phpCode = preg_replace('/\b' . $input . '\b/', '$' . $replacement, $phpCode);
                } else {
                    // probably call to a global function, collect and validate outside
                    $tagItem->JsExpression = preg_replace('/\b_component.' . $input . '\b/', $input, $tagItem->JsExpression);
                    $this->usedFunctions[$input] = true;
                }
            }
        }
        $this->inlineExpressions[] = $tagItem->JsExpression;
        $tagItem->JsExpressionCode = count($this->inlineExpressions) - 1;
        $tagItem->PhpExpression = $phpCode;
    }

    private function appendExpression(TagItem &$tagItem)
    {
        $this->buildExpression($tagItem);
        $this->flushBuffer();
        $this->code .= PHP_EOL . $this->i() . '$_content .= ' . "htmlentities({$tagItem->PhpExpression} ?? '')" . ';';
    }

    private function i(): string
    {
        return ($this->identations[$this->level]
            ?? ($this->identations[$this->level] = str_repeat($this->indentationPattern, $this->level)));
    }
}
