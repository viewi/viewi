<?php

namespace Viewi\TemplateParser;

class TagItemConverter
{
    public static function getRaw(TagItem $tagItem): array
    {
        $node = [];
        $node['c'] = $tagItem->ItsExpression || $tagItem->RawHtml || !$tagItem->Content
            ? $tagItem->Content
            : html_entity_decode($tagItem->Content, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5);
        $node['t'] = isset($tagItem->Type) ? $tagItem->Type->toShort() : 'r';
        if ($node['t'] === 'v') {
            unset($node['t']);
        }
        if ($tagItem->ItsExpression) {
            $node['e'] = 1;
        }
        if ($tagItem->RawHtml) {
            $node['raw'] = 1;
        }
        if ($tagItem->SlotDataKey) {
            $node['slotDataKey'] = $tagItem->SlotDataKey;
        }
        if ($tagItem->ItsExpression) {
            $node['code'] = $tagItem->JsExpressionCode;
            unset($node['c']);
            if ($tagItem->Subscriptions != null) {
                $node['subs'] = $tagItem->Subscriptions;
            }
            if (isset($tagItem->DataExpression)) {
                if ($tagItem->DataExpression->ForData !== null) {
                    $node['forData'] = $tagItem->DataExpression->ForData;
                }
                if ($tagItem->DataExpression->ForKey !== null) {
                    $node['forKey'] = $tagItem->DataExpression->ForKey;
                }
                if ($tagItem->DataExpression->ForKeyAuto) {
                    $node['forKeyAuto'] = 1;
                }
                if ($tagItem->DataExpression->ForItem !== null) {
                    $node['forItem'] = $tagItem->DataExpression->ForItem;
                }
            }
        }
        if (isset($tagItem->DynamicChild)) {
            $node['dynamic'] = self::getRaw($tagItem->DynamicChild);
        }
        if (isset($tagItem->Slots)) {
            $node['slots'] = [];
            foreach ($tagItem->Slots as $slotName => &$child) {
                $node['slots'][$slotName] = self::getRaw($child);
            }
        }
        foreach ($tagItem->getChildren() as &$child) {
            if (
                ($child->Type->Name === TagItemType::TextContent
                    && $child->Skip)
                || ($child->Type->Name === TagItemType::Attribute
                    && $child->Skip)
                || ($child->Type->Name === TagItemType::Tag
                    && $child->Content === 'slotContent'
                )
            ) {
                continue;
            }
            if ($child->Type->Name === TagItemType::Attribute) {
                if (in_array($child->Content, ['if', 'else-if', 'else', 'foreach'])) {
                    if (!isset($node['i'])) {
                        $node['i'] = [];
                    }
                    $node['i'][] = self::getRaw($child);
                } else {
                    if (!isset($node['a'])) {
                        $node['a'] = [];
                    }
                    $node['a'][] = self::getRaw($child);
                }
            } else {
                if (!isset($node['h'])) {
                    $node['h'] = [];
                }
                $node['h'][] = self::getRaw($child);
            }
        }

        return $node;
    }

    public static function prettyOutput(TagItem $tagItem, $indentation = '')
    {
        $output = "";

        $output .= PHP_EOL . $indentation . ($tagItem->Type->Name !== TagItemType::Root ? $tagItem->Content : 'ROOT');
        foreach ($tagItem->getChildren() as &$child) {
            if ($child->Type->Name === TagItemType::Tag) {
                $output .= ":" . self::prettyOutput($child, $indentation . '  ');
            }
        }
        return $output;
    }

    public static function errorOutput(?string $fileOrName, string $html, int $position, int $highlightSize = 3)
    {
        $length = 100;
        if ($position < $length) {
            $length = $position;
        }
        if ($highlightSize <= 0) {
            $highlightSize = 3;
        }
        $start = max(0, $position - $length);
        $line = count(explode(PHP_EOL, substr($html, 0, $position)));
        $before = substr($html, $start, $length - $highlightSize);
        $mid = substr($html, $start + $length - $highlightSize, $highlightSize);
        $after = substr($html, $start + $length, 10);

        $output = self::terminalBold(self::terminalGreen($fileOrName))
            . PHP_EOL
            . "LINE: $line"
            . PHP_EOL
            . $before
            . self::terminalCurlyUnderline(self::terminalBold(self::terminalRed($mid)))
            . $after;

        return $output;
    }

    // https://askubuntu.com/questions/528928/how-to-do-underline-bold-italic-strikethrough-color-background-and-size-i
    public static function terminalGreen(string $text)
    {
        return "\e[38;5;42m$text\e[39m";
    }

    public static function terminalRed(string $text)
    {
        return "\e[31m{$text}\e[0m";
    }

    public static function terminalBold(string $text)
    {
        return "\e[1m{$text}\e[0m";
    }

    public static function terminalCurlyUnderline(string $text)
    {
        return "\e[4:3m{$text}\e[4:0m";
    }
}
