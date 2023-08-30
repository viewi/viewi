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
                if ($tagItem->DataExpression->ForItem !== null) {
                    $node['forItem'] = $tagItem->DataExpression->ForItem;
                }
            }
        }
        if (isset($tagItem->DynamicChild)) {
            $node['dynamic'] = self::getRaw($tagItem->DynamicChild);
        }
        foreach ($tagItem->getChildren() as &$child) {
            if (
                $child->Type->Name === TagItemType::TextContent
                && $child->Skip
            ) {
                continue;
            }
            if ($child->Type->Name === TagItemType::Attribute) {
                if (!isset($node['a'])) {
                    $node['a'] = [];
                }
                $node['a'][] = self::getRaw($child);
            } else {
                if (!isset($node['h'])) {
                    $node['h'] = [];
                }
                $node['h'][] = self::getRaw($child);
            }
        }

        return $node;
    }
}
