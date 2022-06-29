<?php

namespace Viewi\Services;

use Viewi\App;
use Viewi\PageEngine;
use Viewi\TagItem;
use Viewi\TagItemType;

class TreeShakingService
{
    private array $selectors;
    private bool $initiated = false;
    private PageEngine $pageEngine;

    public function __construct(PageEngine $pageEngine)
    {
        $this->pageEngine = $pageEngine;
    }

    private function init()
    {
        if (!$this->initiated) {
            $this->initiated = true;
            $this->selectors = $this->pageEngine->getEvaluatedSelectors();
            $templates = $this->pageEngine->getTemplates();
            foreach ($templates as $pageTemplate) {
                $this->processNode($pageTemplate->RootTag);
            }

            // echo '<pre>';
            // print_r($this->selectors);
        }
    }

    private function processNode(TagItem $node)
    {
        foreach ($node->getChildren() as &$tag) {
            if (!$tag->ItsExpression) {
                switch ($tag->Type->Name) {
                    case TagItemType::Tag: {
                            $this->selectors[$tag->Content] = true; // tag
                            break;
                        }
                    case TagItemType::Attribute: {
                            $this->selectors["[{$tag->Content}]"] = true; // [attribute]
                            if ($tag->Content === 'class') {
                                $values = $this->getChildrenValues($tag);
                                foreach ($values as $class) {
                                    $this->selectors[".$class"] = true; // .class
                                }
                            } else if ($tag->Content === 'id') {
                                $id = $this->getChildrenText($tag);
                                $this->selectors["#$id"] = true; // #id
                            }
                            if ($tag->OriginContents !== null) {
                                foreach ($tag->OriginContents as $originContent) {
                                    if (strpos($originContent, '.') !== false) {
                                        $parts = explode('.', $originContent, 2);
                                        // $this->selectors["[{$parts[0]}]"] = true; // [attribute]
                                        if ($parts[0] === 'class') {
                                            $values = explode(' ', $parts[1]);
                                            foreach ($values as $class) {
                                                $this->selectors[".$class"] = true; // .class
                                            }
                                        } else if ($parts[0] === 'id') {
                                            $this->selectors["#{$parts[1]}"] = true; // #id
                                        }
                                    }
                                }
                            }
                            break;
                        }
                    default: {
                            break;
                        }
                }
            }
            $this->processNode($tag);
        }
    }

    private function getChildrenText(TagItem $node)
    {
        $text = '';
        foreach ($node->getChildren() as &$tag) {
            if (!$tag->ItsExpression) {
                $text .= $tag->Content;
            }
        }
        return $text;
    }

    private function getChildrenValues(TagItem $node)
    {
        $values = [];
        foreach ($node->getChildren() as &$tag) {
            if (!$tag->ItsExpression) {
                $values = array_merge($values, explode(' ', trim($tag->Content)));
            }
        }
        return $values;
    }
    private array $cssTokens = [];
    private int $currentIndex = -1;
    private string $css = '';
    private string $selector = '';
    private int $count;
    private int $i = 0;

    private function reset()
    {
        $this->count = strlen($this->css);
        $this->cssTokens = [];
        $this->selector = '';
        $this->i = 0;
        $this->currentIndex = -1;
    }

    private function newTokensGroup(string $name = 'global', bool $noblock = false)
    {
        $this->currentIndex++;
        $this->cssTokens[] = [
            'scope' => $name,
            'noblock' => $noblock,
            'rules' => []
        ];
    }

    private function addRule($selector, $content)
    {
        $this->cssTokens[$this->currentIndex]['rules'][] = [
            'selector' => $selector,
            'content' => $content,
            'valid' => false
        ];
    }

    private function readCommentBlock()
    {
        $this->i += 2;
        $comment = '';
        while ($this->i < $this->count) {
            switch ($this->css[$this->i]) {
                case '*': {
                        if ($this->i + 1 < $this->count && $this->css[$this->i + 1] === '/') {
                            $this->addRule('##comment##', $comment);
                            $this->i += 2;
                            return;
                        }
                    }
                default: {
                        $comment .= $this->css[$this->i];
                        break;
                    }
            }
            $this->i++;
        }
    }

    private function readGroupName()
    {
        $group = '';
        while ($this->i < $this->count) {
            switch ($this->css[$this->i]) {
                case '{': {
                        return $group;
                    }
                case ';': { // ex: @import 'url'; @charset 'UTF-8';
                        $this->newTokensGroup($group, true);
                        $this->selector = '';
                        return 'global';
                    }
                default: {
                        $group .= $this->css[$this->i];
                        break;
                    }
            }
            $this->i++;
        }
    }

    private function readRuleContentBlock()
    {
        $this->i++;
        $content = '';
        $nestedCount = 0;
        while ($this->i < $this->count) {
            switch ($this->css[$this->i]) {
                case '}': {
                        if ($nestedCount == 0) {
                            $this->addRule($this->selector, $content);
                            $this->i += 1;
                            return;
                        } else {
                            $nestedCount--;
                        }
                        $content .= $this->css[$this->i];
                        break;
                    }
                case '{': {
                        $nestedCount++;
                    }
                default: {
                        $content .= $this->css[$this->i];
                        break;
                    }
            }
            $this->i++;
        }
    }

    private function shakeCssContent()
    {
        while ($this->i < $this->count) {
            switch ($this->css[$this->i]) {
                case '/': {
                        if ($this->i + 1 < $this->count && $this->css[$this->i + 1] === '*') {
                            // comment /*
                            $this->readCommentBlock();
                            break;
                        }
                    }
                case '@': {
                        if ($this->i - 1 >= 0 && $this->css[$this->i - 1] !== '\\') {
                            // new group, ex: @media, @animation, @font-face
                            $group = $this->readGroupName();
                            $this->newTokensGroup($group);
                            $this->selector = '';
                            break;
                        } else {
                            $this->selector .= $this->css[$this->i];
                            break;
                        }
                    }
                case '{': {
                        // rule content
                        $this->readRuleContentBlock();
                        $this->selector = '';
                        break;
                    }
                case '}': {
                        $this->newTokensGroup();
                        $this->selector = '';
                        break;
                    }
                default: {
                        $this->selector .= $this->css[$this->i];
                        break;
                    }
            }
            $this->i++;
        }
    }

    private function validateRules()
    {
        $keepNext = false;
        foreach ($this->cssTokens as &$group) {
            $group['valid'] = false;
            $group['special'] = false;
            $groupName = trim($group['scope']);
            if ($groupName[0] === '@' && strpos($groupName, '@media') === false) {
                $group['valid'] = true;
                $group['special'] = true;
                continue;
            }
            foreach ($group['rules'] as &$rule) {
                $selector = trim($rule['selector']);
                $keepCurrent = $keepNext;
                $keepNext = false;
                if ($selector === '##comment##') {
                    $rule['valid'] = false;
                    if (strpos($rule['content'], '@keep') !== false) {
                        $keepNext = true;
                    }
                    continue;
                }
                if ($keepCurrent) {
                    $rule['valid'] = true;
                } else {
                    // wrong
                    // .uk-nav.uk-nav-divider> :not(.uk-nav-divider)+ :not(.uk-nav-header
                    // right
                    // .uk-nav.uk-nav-divider> :not(.uk-nav-divider)+ :not(.uk-nav-header, .uk-nav-divider) {
                    //     margin-top: 0;
                    //     padding-top: 0;
                    //     border-top: 1px solid #e5e5e5;
                    // }
                    // fix:
                    // .uk-breadcrumb> :nth-child(n+2):not(.uk-first-column)::before
                    $selectors = explode(',', $selector);
                    $takeAll = strpos($selector, '(') !== false;
                    $validatedSelectors = [];
                    foreach ($selectors as $subSelectorText) {
                        $originalSubSelector = trim($subSelectorText);
                        // print_r($originalSubSelector . ' before ' . PHP_EOL);
                        $subSelector = str_replace(['[', '*', '(', ')', 'n+'], ':', $originalSubSelector);
                        $subSelector = str_replace(['>', '+', '~', ' '], '!', $subSelector);
                        // print_r($subSelector . ' after ' . PHP_EOL); // TODO: fix browser error when printing debug info
                        $subSelector = str_replace('\\', '', $subSelector);

                        $allValid = true;
                        $subSelectorParts = explode('!', $subSelector);                        
                        foreach ($subSelectorParts as $subSelectorPart) {
                            $specialPos = strpos($subSelectorPart, ':');
                            if ($specialPos !== false) {
                                $subSelectorPart = substr($subSelectorPart, 0, $specialPos);
                            }
                            if (strpos($subSelectorPart, '.') !== false) {
                                $parts = explode('.', $subSelectorPart, 3);
                                $subSelectorPart = trim($parts[0]);
                                if (!$subSelectorPart) {
                                    $subSelectorPart = trim('.' . $parts[1]);
                                }
                            }
                            // if ($originalSubSelector === '.uk-breadcrumb> :nth-child(n+2):not(.uk-first-column)::before') {
                            //     print_r([
                            //         $originalSubSelector,
                            //         $subSelectorParts,
                            //         $subSelector,
                            //         $subSelectorPart
                            //     ]);
                            // }
                            $allValid = $allValid && (!$subSelectorPart || isset($this->selectors[$subSelectorPart]));
                        }
                        if ($allValid) {
                            $validatedSelectors[] = $originalSubSelector;
                        }
                        // if ($subSelector && $subSelector[0] == '[') {
                        //     print_r($subSelector . ' - ');
                        // }
                        $rule['valid'] = $rule['valid'] || !$subSelector || $allValid;
                    }
                    if (!$takeAll) {
                        $rule['selectors'] = $validatedSelectors;
                    }
                    // if ($originalSubSelector === '.uk-navbar-nav') {
                    //     print_r([
                    //         $rule,
                    //         $validatedSelectors
                    //     ]);
                    // }
                }
                $group['valid'] = $group['valid'] || $rule['valid'];
            }
        }
    }

    private function getShakenCss()
    {
        $textCss = '';
        $indentation = '    ';
        foreach ($this->cssTokens as $group) {
            if ($group['valid']) {
                $groupName = trim($group['scope']);
                if ($group['noblock']) {
                    $textCss .= $groupName . ';' . PHP_EOL;
                    continue;
                }
                $blocked = $groupName !== 'global';
                if ($blocked) {
                    $textCss .= $groupName . ' {' . PHP_EOL;
                }
                foreach ($group['rules'] as $rule) {
                    $selector = implode(',' . PHP_EOL, $rule['selectors'] ?? [trim($rule['selector'])]); // trim($rule['selector']);
                    // $selector = trim($rule['selector']); // trim($rule['selector']);
                    if ($rule['valid'] || $group['special']) {
                        $textCss .= ($blocked ? $indentation : '') . $selector . ' {' . PHP_EOL;
                        $textCss .= $indentation . ($blocked ? $indentation : '') . trim($rule['content']);
                        $textCss .= $blocked ?  PHP_EOL . $indentation . '}' . PHP_EOL :  PHP_EOL . '}' . PHP_EOL . PHP_EOL;
                    }
                }
                if ($blocked) {
                    $textCss .= '}' . PHP_EOL . PHP_EOL;
                }
            }
        }
        return $textCss;
    }

    public function shakeCss(string $cssText): string
    {
        $this->init();
        $this->css = $cssText;
        $this->reset();
        $this->newTokensGroup();
        $this->shakeCssContent();
        $this->validateRules();
        // echo '<pre>';
        // print_r($this->cssTokens);
        // print_r($this->selectors);
        return $this->getShakenCss();
    }
}
