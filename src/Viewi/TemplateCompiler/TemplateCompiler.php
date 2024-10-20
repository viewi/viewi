<?php

namespace Viewi\TemplateCompiler;

use Exception;
use Throwable;
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
    const UndefinedValue = '___undefined___';
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
    /** @var array<string,string> */
    private array $booleanAttributes;
    private string $booleanAttributesString = 'async,autofocus,autoplay,checked,controls,' .
        'default,defer,disabled,formnovalidate,hidden,ismap,itemscope,loop,' .
        'multiple,muted,nomodule,novalidate,open,readonly,required,reversed,' .
        'selected';
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
    private array $localScopeArguments = [];
    private array $usedFunctions = [];
    private array $inlineExpressions = [];
    private array $usedComponents = [];
    private bool $hasHtmlTag = false;
    private $nullVar = null;
    private array $renderedComponents = [];
    private array $globalEntries = [];
    private array $nestedDependencies = [];

    public function __construct(private JsTranspiler $jsTranspiler)
    {
        $this->voidTags = array_flip(explode(',', $this->voidTagsString));
        $this->booleanAttributes = array_flip(explode(',', $this->booleanAttributesString));
    }

    public function getBooleanAttributesString()
    {
        return $this->booleanAttributesString;
    }

    public function setGlobals(array $globals)
    {
        $this->globalEntries = $globals;
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
            $this->inlineExpressions,
            $this->hasHtmlTag,
            $this->usedComponents
        );
    }

    public function getRenderInvokations(): array
    {
        return $this->renderedComponents;
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
            $this->hasHtmlTag = false;
            $this->usedComponents = [];
            $this->renderedComponents = [];
            $this->nestedDependencies = [];
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
                        $this->inlineExpressions[] = [$jsOutput->__toString(), $this->localScopeArguments];
                        $foreachTagValue->DataExpression->ForData = count($this->inlineExpressions) - 1;
                        $transforms = $jsOutput->getTransforms();
                        foreach ($transforms as $input => $replacement) {
                            if ($input[0] === '$') {
                                $phpCode = preg_replace('/\$\b' . substr($input, 1) . '\b/', '$' . $replacement, $phpCode);
                            } else {
                                $phpCode = preg_replace('/\b' . $input . '\b/', '$' . $replacement, $phpCode);
                            }
                        }
                        $subs = $jsOutput->getDeps();
                        $classSubs = $this->buildItem->JsOutput->getDeps();
                        $foreachTagValue->Subscriptions = array_keys($this->collectSubscriptions($subs, $classSubs));
                        $foreachTagValue->PhpExpression = $phpCode;
                        $foreachLoop = $foreachParts[1];
                        $foreachAsParts = explode('=>', $foreachLoop);
                        $prevScope = $this->localScope;
                        $prevScopeArguments = $this->localScopeArguments;
                        if (count($foreachAsParts) > 1) {
                            $jsOutput = $this->jsTranspiler->convert($foreachAsParts[0], true, null, $this->localScope);
                            $foreachTagValue->DataExpression->ForKey = $jsOutput->__toString();
                            $argument = trim($foreachAsParts[0]);
                            if ($argument[0] === '$') {
                                $argument = substr($argument, 1);
                            }
                            $this->localScope[$argument] = true;
                            $this->localScopeArguments[] = $argument;
                            $jsOutput = $this->jsTranspiler->convert($foreachAsParts[1], true, null, $this->localScope);
                            $foreachTagValue->DataExpression->ForItem = $jsOutput->__toString();
                            $argument = trim($foreachAsParts[1]);
                            if ($argument[0] === '$') {
                                $argument = substr($argument, 1);
                            }
                            $this->localScope[$argument] = true;
                            $this->localScopeArguments[] = $argument;
                        } else {
                            $autoForKey = $this->getNextIterationKey();
                            $foreachTagValue->DataExpression->ForKey = $autoForKey;
                            $foreachTagValue->DataExpression->ForKeyAuto = true;
                            $jsOutput = $this->jsTranspiler->convert($foreachAsParts[0], true, null, $this->localScope);
                            $foreachTagValue->DataExpression->ForItem = $jsOutput->__toString();
                            $argument = trim($foreachAsParts[0]);
                            if ($argument[0] === '$') {
                                $argument = substr($argument, 1);
                            }
                            $this->localScope[$autoForKey] = true;
                            $this->localScopeArguments[] = $autoForKey;
                            $this->localScope[$argument] = true;
                            $this->localScopeArguments[] = $argument;
                        }
                        $this->flushBuffer();
                        $this->code .= PHP_EOL . $this->i() . "foreach ({$foreachTagValue->PhpExpression} as $foreachLoop) {";
                        $this->level++;
                        // TODO: pass parent subscriptions
                        $this->nestedDependencies[$argument] = $foreachTagValue->Subscriptions;
                        // print_r([$argument, $this->nestedDependencies]);
                        $this->buildTag($tagItem);
                        unset($this->nestedDependencies[$argument]);
                        $this->flushBuffer();
                        $this->level--;
                        $this->code .= PHP_EOL . $this->i() . "}";
                        $this->localScope = $prevScope;
                        $this->localScopeArguments = $prevScopeArguments;
                        return;
                        // == FOREACH END ==
                    }
                    $attributes[] = $child;
                } else {
                    $children[] = $child;
                }
            }
        }
        $childrenCount = count($children);
        $hasChildren = $childrenCount > 0;
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
            } elseif ($tagItem->Content === 'html') {
                $this->hasHtmlTag = true;
            }
        }
        $component = $tagItem->Type->Name === TagItemType::Component;
        $expression = $tagItem->ItsExpression;
        $nested = $root || $tag || $component || $slot || $slotContent || $templateTag;
        if ($component) {
            $this->usedComponents[$tagItem->Content] = true;
        }
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
                $this->slots[] = [$slotContentRawName, $slotFunction, $slotRoot];
                // Helpers::debug($slotFunction);
                // $tagItem->setChildren([]);
                return;
            }
            // == COMPONENT ==
            if ($component) {
                $this->flushBuffer();
                $renderedComponentName = !$tagItem->PhpExpression ? $tagItem->Content : null;
                $renderedComponentProps = [];
                if ($renderedComponentName && !isset($this->renderedComponents[$renderedComponentName])) {
                    $this->renderedComponents[$renderedComponentName] = [];
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
                        $isEvent = $attributeItem->Content[0] === '(';
                        $itsModel = $attributeItem->Content === 'model';
                        $backupLocalScope = $this->localScope;
                        $backupLocalScopeArg = $this->localScopeArguments;
                        if ($isEvent || $itsModel) {
                            $combinedExpression = '';
                            foreach ($values as &$subValue) {
                                $combinedExpression .= $subValue->Content;
                            }
                            $attributeTagValue = new TagItem();
                            $attributeTagValue->Type = new TagItemType(TagItemType::AttributeValue);
                            $attributeTagValue->ItsExpression = true;
                            if ($itsModel) {
                                $combinedExpression = "[function (\${$this->_CompileJsComponentName}) { return $combinedExpression; }, function (\${$this->_CompileJsComponentName}, \$value) { $combinedExpression = \$value; }]";
                            }
                            $attributeTagValue->Content = $combinedExpression;
                            $values = [$attributeTagValue];
                            $attributeItem->setChildren($values);
                            if ($isEvent) {
                                $this->localScope['event'] = true;
                                $this->localScopeArguments[] = 'event';
                            }
                        }

                        $concat = '';
                        $single = count($values) === 1;

                        foreach ($values as &$attributeValue) {
                            if ($attributeValue->ItsExpression) {
                                $this->buildExpression($attributeValue);
                                $combinedValue .= $concat . ($single ? $attributeValue->PhpExpression : "({$attributeValue->PhpExpression})");
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
                        if ($isEvent) {
                            $this->localScope = $backupLocalScope;
                            $this->localScopeArguments = $backupLocalScopeArg;
                            $jsEventCode = $values[0]->JsExpression;
                            if (!ctype_alnum(str_replace(['_', '->', '$'], '', $combinedValue))) { // closure
                                $jsEventCode = "function () { $jsEventCode; }";
                            } else {
                                $jsEventCode = "function (event) { $jsEventCode(event); }";
                            }
                            $lastExpression = array_pop($this->inlineExpressions);
                            $lastExpression[0] = $jsEventCode;
                            $values[0]->JsExpression = $jsEventCode;
                            $this->inlineExpressions[] = $lastExpression;
                            // Helpers::debug([$values[0], $this->inlineExpressions]);
                            $combinedValue = "true";
                        }
                        $name = $attributeItem->PhpExpression ?? var_export($attributeItem->Content, true);
                        if ($renderedComponentName && !$attributeItem->PhpExpression) {
                            try {
                                $parsedPropValue = self::UndefinedValue;
                                $assignCode = "\$parsedPropValue = $combinedValue;";
                                @eval($assignCode);
                                if ($parsedPropValue !== self::UndefinedValue) {
                                    $renderedComponentProps[$attributeItem->Content] = $parsedPropValue;
                                }
                            } catch (Throwable $_) {
                                // silent
                            }
                        }
                        $inputArguments[] = "{$comma}$name => $combinedValue";
                        $comma = ',' . PHP_EOL . $this->i();
                    }
                    $this->level--;
                }
                if ($renderedComponentName) {
                    $this->renderedComponents[$renderedComponentName][] = $renderedComponentProps;
                }
                // print_r([$renderedComponentName, $this->renderedComponents[$renderedComponentName]]);
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
                    $this->slots[] = ['default', $slotFunction, $slotRoot];
                    $tagItem->addSlot('default', $slotRoot);
                    $defaultRenderName = var_export($slotFunction->renderName, true);
                    $passThroughSlots[] = "{$comma}'default' => $defaultRenderName";
                    $trackMap = ['default' => true];
                    $comma = ',' . PHP_EOL . $this->i();
                }
                foreach ($slotFunction->slots as $childSlot) {
                    $this->slots[] = $childSlot;
                    $nextSlotName = $childSlot[0];
                    // $nextSlotId = 0;
                    // while (isset($tagItem->Slots[$nextSlotName])) {
                    //     $nextSlotName = $childSlot[0] . '_' . (++$nextSlotId);
                    // }
                    if (!isset($tagItem->Slots[$nextSlotName])) {
                        $tagItem->addSlot($nextSlotName, $childSlot[2]);
                    }
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

                $this->code .= PHP_EOL . $this->i() . "\$_content .= \$_engine->renderComponent($componentName, \$_component, [$props], [$slotsMap], $scope);";
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
                    $this->buildAttributes($attributes);
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
                    // for text nodes merge
                    /**
                     * @var TagItem[]
                     */
                    $textCollection = [];
                    $textsCout = 0;
                    $lastChild = false;
                    $lastIndex = $childrenCount - 1;
                    foreach ($children as $order => &$childItem) {
                        $notText = true;
                        $raw = false;
                        if (
                            $childItem->Type->Name === TagItemType::TextContent
                        ) {
                            if ($childItem->ItsExpression && $childItem->Content[0] === '{') {
                                $raw = true;
                            } else {
                                $textCollection[] = &$childItem;
                                $textsCout++;
                                $notText = false;
                            }
                        }
                        $lastChild = $lastIndex === $order;
                        if ($notText || $lastChild) {
                            if ($textsCout > 0) {
                                $textTagItem = &$textCollection[0];
                                if ($textsCout > 1) {
                                    if (!$textTagItem->ItsExpression) {
                                        $textTagItem->Content = var_export(html_entity_decode($textTagItem->Content, ENT_HTML5), true);
                                    }
                                    if ($textTagItem->ItsExpression) {
                                        $textTagItem->Content = '(' . $textTagItem->Content . ')';
                                    }
                                    for ($textI = 1; $textI < $textsCout; $textI++) {
                                        $neigbourText = &$textCollection[$textI];
                                        $textTagItem->ItsExpression =
                                            $textTagItem->ItsExpression || $neigbourText->ItsExpression;
                                        $textTagItem->Content .= ' . ' .
                                            ($neigbourText->ItsExpression
                                                ? '((' . $neigbourText->Content . ') ?? \'\')'
                                                : var_export(html_entity_decode($neigbourText->Content, ENT_HTML5), true));
                                        $neigbourText->Skip = true;
                                    }
                                    $textTagItem->Content = '(' . $textTagItem->Content . ')';
                                }
                                if ($textTagItem->ItsExpression) {
                                    $this->appendExpression($textTagItem);
                                } else {
                                    $this->plainItems[] = $textTagItem->Content;
                                }
                                $textCollection = [];
                                $textsCout = 0;
                            }
                            if ($childItem->Type->Name === TagItemType::Comment) {
                                $this->plainItems[] = '<!--' . htmlentities($childItem->Content) . '-->';
                            } elseif ($raw) {
                                $this->appendExpression($childItem);
                                // Helpers::debug([$childItem->Type->Name, $childItem->Content]);
                            } elseif ($notText) {
                                $this->buildTag($childItem);
                            }
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
        return $this->nullVar;
    }

    /**
     * 
     * @param TagItem[] $attributes 
     * @return void 
     * @throws Exception 
     */
    private function buildAttributes(array &$attributes)
    {
        /**
         * @var TagItem[]
         */
        $attributeMap = [];
        foreach ($attributes as &$attributeItem) {
            $attributeName = $attributeItem->Content;
            $valueToReplace = false;
            if (!$attributeItem->ItsExpression && strpos($attributeName, '.') !== false) {
                $parts = explode('.', $attributeName, 2);
                $attributeName = $parts[0];
                $valueToReplace = $parts[1];
                $attributeItem->Content = $attributeName;
            }
            $mutated = isset($attributeMap[$attributeName]);
            $attributeItem->Skip = $mutated;
            $attributeChildren = $attributeItem->getChildren();
            if (count($attributeChildren) > 0) {
                foreach ($attributeChildren as $aci => &$attributeChild) {
                    $space = $mutated && $aci === 0 ? ' ' : '';
                    if ($valueToReplace) {
                        if ($attributeChild->ItsExpression) {
                            $attributeChild->Content = "{$attributeChild->Content} ? '{$space}$valueToReplace' : ''";
                        } else {
                            $attributeChild->Content = $space . $attributeChild->Content;
                        }
                    }
                    if ($mutated) {
                        $attributeMap[$attributeName]->addChild($attributeChild);
                    } else {
                        $attributeMap[$attributeName] = $attributeItem;
                    }
                }
            } else {
                if (!$mutated) {
                    $attributeMap[$attributeName] = $attributeItem;
                }
            }
        }
        foreach ($attributeMap as &$attributeItem) {
            $this->buildAttribute($attributeItem);
        }
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
        }
        if ($attributeItem->Content[0] === '#') {
            return;
        }
        $itsModel = $attributeItem->Content === 'model';
        $itsEvent = $attributeItem->Content[0] === '(';
        // $itsRef = $attributeItem->Content[0] === '#';
        if (!$attributeItem->Content || $itsEvent || $attributeItem->ItsExpression || $itsModel) {
            $expression = '';
            foreach ($children as &$subValue) {
                $expression .= $subValue->Content;
            }
            $attributeTagValue = null;
            if ($attributeItem->ItsExpression) {
                $attributeTagValue = new TagItem();
                $attributeTagValue->Type = new TagItemType(TagItemType::AttributeValue);
            } else {
                $attributeTagValue = &$children[0];
            }
            if ($itsModel) {
                $expression = "[function (\${$this->_CompileJsComponentName}) { return $expression; }, function (\${$this->_CompileJsComponentName}, \$value) { $expression = \$value; }]";
            }
            $attributeTagValue->ItsExpression = true;
            $attributeTagValue->Content = $expression;
            $backupLocalScope = $this->localScope;
            $backupLocalScopeArg = $this->localScopeArguments;
            //if ($itsEvent) {
            // $this->localScope = ['event' => true];
            // $this->localScopeArguments = ['event'];
            //} else {
            $this->localScope['event'] = true;
            $this->localScopeArguments[] = 'event';
            //}
            $this->buildExpression($attributeTagValue);
            $this->localScope = $backupLocalScope;
            $this->localScopeArguments = $backupLocalScopeArg;
            $jsEventCodeTupple = array_pop($this->inlineExpressions);
            $jsEventCode = $jsEventCodeTupple[0];
            $funcArguments = $itsEvent ? 'event' : implode(', ', $jsEventCodeTupple[1]);
            if (!$itsModel) {
                if (!ctype_alnum(str_replace(['_', '->', '$'], '', $expression))) { // closure
                    $jsEventCode = "function ($funcArguments) { $jsEventCode; }";
                } else {
                    $jsEventCode = "function ($funcArguments) { $jsEventCode($funcArguments); }";
                }
            }
            $this->inlineExpressions[] = [$jsEventCode, $this->localScopeArguments];
            if (!$attributeItem->ItsExpression) {
                $attributeItem->setChildren([$attributeTagValue]);
                return; // event is handled on front-end only
            } else {
                $attributeItem->DynamicChild = $attributeTagValue;
            }
        }

        if ($childrenCount === 1 && $children[0]->ItsExpression) {
            $attributeValue = &$children[0];
            $this->buildExpression($attributeValue);
            $this->flushBuffer();
            $this->code .= PHP_EOL . $this->i() . "\$tempVal = {$attributeValue->PhpExpression};";
            if (isset($this->booleanAttributes[strtolower($attributeItem->Content)])) {
                // boolean attribute
                $this->code .= PHP_EOL . $this->i() . 'if ($tempVal) {';
                $this->level++;
                $this->code .= PHP_EOL . $this->i() . '$_content .= ' . var_export(' ' . $attributeItem->Content . '="' . $attributeItem->Content . '"', true) . ';';
                $this->level--;
                $this->code .= PHP_EOL . $this->i() . "}";
            } else {
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
            }
        } else {
            if ($attributeItem->ItsExpression) {
                $this->code .= PHP_EOL . $this->i() . '$_content .= ' . "' ' . htmlentities({$attributeItem->PhpExpression} ?? '')" . ';';
            } else {
                $this->plainItems[] = ' ' . $attributeItem->Content;
            }
            if ($childrenCount > 0) {
                $this->plainItems[] =  '="';
                foreach ($children as &$attributeValue) {
                    if ($attributeValue->ItsExpression) {
                        $this->appendExpression($attributeValue);
                    } else {
                        $this->plainItems[] = htmlentities($attributeValue->Content);
                    }
                }
                $this->plainItems[] = '"';
            } elseif (isset($this->booleanAttributes[strtolower($attributeItem->Content)])) {
                $this->plainItems[] =  "=\"{$attributeItem->Content}\"";
            }
        }

        if ($attributeItem->ItsExpression) {
            $this->flushBuffer();
            $this->level--;
            $this->code .= PHP_EOL . $this->i() . "}";
        }
    }

    private function collectSubscriptions(array $subs, array &$classSubs)
    {
        $result = [];
        $repeat = true;
        while ($repeat) {
            $repeat = false;
            $pending = $subs;
            foreach ($pending as $name => $_1) {
                if (!isset($result[$name])) {
                    $result[$name] = true;
                    $parts = explode('.', $name, 2);
                    $parent = array_pop($parts);
                    if (isset($this->nestedDependencies[$parent])) {
                        foreach ($this->nestedDependencies[$parent] as $key) {
                            $result[$key] = true;
                        }
                        // print_r([$name, $result]);
                    }
                    foreach ($classSubs as $className => $methods) {
                        if (isset($methods[$name])) {
                            $repeat = true;
                            $subs = array_merge($methods[$name], $subs);
                            break;
                        }
                    }
                }
            }
        }
        // foreach ($this->nestedDependencies as $parent => $list) {
        //     foreach ($list as $key) {
        //         $result[$key] = true;
        //     }
        // }
        return $result;
    }

    private function buildExpression(TagItem &$tagItem)
    {
        if ($tagItem->PhpExpression) {
            return;
        }
        $phpCode = $tagItem->Content;
        if ($phpCode[0] === '{' && $phpCode[strlen($phpCode) - 1] === '}') {
            $phpCode = substr($phpCode, 1, strlen($phpCode) - 2);
            $tagItem->RawHtml = true;
            // Helpers::debug([$phpCode, $tagItem->Content]);
        }
        $jsOutput = $this->jsTranspiler->convert($phpCode, true, $this->_CompileJsComponentName, $this->localScope);

        // if ($phpCode === '$user->name') {
        //     Helpers::debug([$tagItem->JsExpression, $phpCode, $jsOutput->getDeps()]);
        // }
        $tagItem->JsExpression = $jsOutput->__toString();
        $transforms = $jsOutput->getTransforms();
        $subsIncluded = false;
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
                if ($tagItem->Subscriptions === null) {
                    $tagItem->Subscriptions = [];
                }
                $subs = $jsOutput->getDeps();
                $classSubs = $this->buildItem->JsOutput->getDeps();
                $tagItem->Subscriptions = array_keys($this->collectSubscriptions($subs, $classSubs));
                $subsIncluded = true;
            } else {
                if (
                    isset($this->buildItem->publicNodes[$input])
                    && $this->buildItem->publicNodes[$input] === ExportItem::Method
                ) {
                    $phpCode = preg_replace('/\b' . $input . '\b/', '$' . $replacement, $phpCode);
                    $subs = $jsOutput->getDeps();
                    $subs[$input] = true;
                    $classSubs = $this->buildItem->JsOutput->getDeps();
                    $tagItem->Subscriptions = array_keys($this->collectSubscriptions($subs, $classSubs));
                    $subsIncluded = true;
                } else {
                    // probably call to a global function, collect and validate outside
                    if (isset($this->globalEntries[$input])) {
                        $phpCode = preg_replace('/\b' . $input . '\b\(/', "\$_engine->call('$input', ", $phpCode);
                        // print_r([
                        //     $phpCode,
                        //     $tagItem->JsExpression,
                        //     $input
                        // ]);
                    } else {
                        $tagItem->JsExpression = preg_replace('/\b_component.' . $input . '\b/', $input, $tagItem->JsExpression);
                        $this->usedFunctions[$input] = true;
                    }
                }
            }
        }
        if (!$subsIncluded) {
            $subs = $jsOutput->getDeps();
            $classSubs = $this->buildItem->JsOutput->getDeps();
            $tagItem->Subscriptions = array_keys($this->collectSubscriptions($subs, $classSubs));
        }
        if (
            !isset($this->localScope[$tagItem->JsExpression])
            && ctype_alnum(str_replace('_', '', $tagItem->JsExpression))
            && isset($this->buildItem->publicNodes[$tagItem->JsExpression])
            && $this->buildItem->publicNodes[$tagItem->JsExpression] === ExportItem::Method
        ) { // method
            $tagItem->JsExpression = $this->_CompileJsComponentName .
                '.' . $tagItem->JsExpression;
        }

        $this->inlineExpressions[] = [$tagItem->JsExpression, $this->localScopeArguments];
        $tagItem->JsExpressionCode = count($this->inlineExpressions) - 1;
        $tagItem->PhpExpression = $phpCode;
        // if ($tagItem->JsExpression === '_component.user.name') {
        //     Helpers::debug([$tagItem->JsExpression, $phpCode, $tagItem->Subscriptions]);
        // }
    }

    private function appendExpression(TagItem &$tagItem)
    {
        $this->buildExpression($tagItem);
        $this->flushBuffer();
        $this->code .= PHP_EOL . $this->i() . '$_content .= ' . ($tagItem->RawHtml ? $tagItem->PhpExpression : "htmlentities({$tagItem->PhpExpression} ?? '')") . ';';
    }

    private function i(): string
    {
        return ($this->identations[$this->level]
            ?? ($this->identations[$this->level] = str_repeat($this->indentationPattern, $this->level)));
    }
}
