<?php

namespace Viewi\Services;

use Viewi\App;
use Viewi\TagItem;
use Viewi\TagItemType;

class TreeShakingService
{
    private static array $selectors;
    private static bool $inited = false;

    private static function init()
    {
        if (!self::$inited) {
            self::$inited = true;
            self::$selectors = [];
            $templates = App::getEngine()->getTemplates();
            foreach ($templates as $pageTemplate) {
                self::processNode($pageTemplate->RootTag);
            }
            // echo '<pre>';
            // print_r(self::$selectors);
        }
    }

    private static function processNode(TagItem $node)
    {
        foreach ($node->getChildren() as &$tag) {
            if (!$tag->ItsExpression) {
                switch ($tag->Type->Name) {
                    case TagItemType::Tag: {
                            self::$selectors[$tag->Content] = true; // tag
                            break;
                        }
                    case TagItemType::Attribute: {
                            self::$selectors["[{$tag->Content}]"] = true; // [attribute]
                            if ($tag->Content === 'class') {
                                $values = self::getChildrenValues($tag);
                                foreach ($values as $class) {
                                    self::$selectors[".$class"] = true; // .class
                                }
                            } else if ($tag->Content === 'id') {
                                $id = self::getChildrenText($tag);
                                self::$selectors["#$id"] = true; // #id
                            }
                            if ($tag->OriginContent !== null && strpos($tag->OriginContent, '.') !== false) {
                                $parts = explode('.', $tag->OriginContent, 2);
                                // self::$selectors["[{$parts[0]}]"] = true; // [attribute]
                                if ($parts[0] === 'class') {
                                    $values = explode(' ', $parts[1]);
                                    foreach ($values as $class) {
                                        self::$selectors[".$class"] = true; // .class
                                    }
                                } else if ($parts[0] === 'id') {
                                    self::$selectors["#{$parts[1]}"] = true; // #id
                                }
                            }
                            break;
                        }
                    default: {
                            break;
                        }
                }
            }
            self::processNode($tag);
        }
    }

    private static function getChildrenText(TagItem $node)
    {
        $text = '';
        foreach ($node->getChildren() as &$tag) {
            if (!$tag->ItsExpression) {
                $text .= $tag->Content;
            }
        }
        return $text;
    }

    private static function getChildrenValues(TagItem $node)
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
                        // new group, ex: @media, @animation, @font-face
                        $group = $this->readGroupName();
                        $this->newTokensGroup($group);
                        $this->selector = '';
                        break;
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
                    $selectors = explode(',', $selector);
                    $validatedSelectors = [];
                    foreach ($selectors as $subSelectorText) {
                        $subSelector = trim($subSelectorText);
                        $subSelector = str_replace(['>', '+', '~', ' '], ':', $subSelector);
                        $specialPos = strpos($subSelector, ':');
                        if ($specialPos !== false) {
                            $subSelector = substr($subSelector, 0, $specialPos);
                        }
                        if (strpos($subSelector, '.') !== false) {
                            $parts = explode('.', $subSelector, 3);
                            $subSelector = trim($parts[0]);
                            if (!$subSelector) {
                                $subSelector = trim('.' . $parts[1]);
                            }
                        }
                        $validatedSelectors[] = $subSelector;
                        $rule['valid'] = $rule['valid'] || isset(self::$selectors[$subSelector]);
                    }
                    $rule['selectors'] = $validatedSelectors;
                }
                $group['valid'] = $group['valid'] || $rule['valid'];
            }
        }
    }

    private function getShakenCss()
    {
        $textCss = '';
        $indentation = '  ';
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
                    $selector = trim($rule['selector']);
                    if ($rule['valid'] || $group['special']) {
                        $textCss .= ($blocked ? $indentation : '') . $selector . ' {' . PHP_EOL;
                        $textCss .= $indentation . ($blocked ? $indentation : '') . trim($rule['content']);
                        $textCss .= $blocked ?  PHP_EOL . $indentation . '}' . PHP_EOL :  PHP_EOL . PHP_EOL . '}' . PHP_EOL . PHP_EOL;
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
        self::init();
        $this->css = $cssText;
        $this->reset();
        $this->newTokensGroup();
        $this->shakeCssContent();
        $this->validateRules();

        // echo '<pre>';
        // print_r($this->cssTokens);

        return $this->getShakenCss();
    }
}
