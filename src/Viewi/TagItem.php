<?php

namespace Viewi;

use Exception;

class TagItem
{
    public ?string $OriginContent = null;
    public ?array $OriginContents = null;
    public ?string $Content = null;
    public TagItemType $Type;
    public bool $ItsExpression = false;
    public bool $Skip = false;
    private ?TagItem $Parent;
    public ?TagItem $DynamicChild;
    public ?string $JsExpression = null;
    public ?string $PhpExpression = null;
    public ?string $PropValueExpression = null;
    public ?array $Subscriptions = null;
    public bool $RawHtml = false;
    public ?DataExpression $DataExpression = null;
    /** @var TagItem[] */
    private ?array $Children;

    // getRaw V2 (using arrays, saves up to x4 in size, in progress)
    public function getRawV2()
    {
        $primaryItem = $this->ItsExpression
            ? [$this->JsExpression]
            : ($this->Content && !$this->RawHtml ? html_entity_decode($this->Content) : $this->Content);
        $hasType = isset($this->Type);
        $type = $hasType ? $this->Type->Name : 'root';
        $hasSubscriptions = false;
        if ($this->ItsExpression) {
            if ($this->Subscriptions !== null) {
                $hasSubscriptions = true;
                $primaryItem[] = $this->Subscriptions;
            }
            if (isset($this->DataExpression)) {
                $foreachData = [];
                if ($this->DataExpression->ForData !== null) {
                    $foreachData['forData'] = $this->DataExpression->ForData;
                }
                if ($this->DataExpression->ForKey !== null) {
                    $foreachData['forKey'] = $this->DataExpression->ForKey;
                }
                if ($this->DataExpression->ForItem !== null) {
                    $foreachData['forItem'] = $this->DataExpression->ForItem;
                }
                if (!$hasSubscriptions) {
                    $primaryItem[] = 0;
                    $hasSubscriptions = true;
                }
                $primaryItem[] = $foreachData;
            }
        }

        if ($this->RawHtml) {
            $primaryItem = [$primaryItem, 2];
        } else if ($type === TagItemType::Comment) {
            $primaryItem = [$primaryItem, 1];
        }

        if (
            $type === TagItemType::TextContent
            || $type === TagItemType::AttributeValue
            || $type === TagItemType::Comment
        ) {
            return $primaryItem;
        }
        if (isset($this->DynamicChild)) {
            if (!$hasSubscriptions) {
                $primaryItem[] = 0;
            }
            $primaryItem[] = $this->DynamicChild->getRaw();
        }
        $node = [];
        if ($hasType || $this->Content) {
            $node[] = $primaryItem;
        }
        // if ($this->Type->Name === TagItemType::Attribute) {
        //     $node[] = $this->ItsExpression ? [$this->JsExpression] : $this->Content;
        // }

        if (isset($this->Children)) {
            $children = [];
            $attributes = [];
            foreach ($this->Children as &$child) {
                if ($child->Type->Name === TagItemType::Attribute) {
                    $attributes[] = $child->getRaw();
                } else {
                    if (
                        $child->Type->Name === TagItemType::TextContent
                        && $child->Skip
                    ) {
                        continue;
                    }
                    $children[] = $child->getRaw();
                }
            }
            $hasChildren = count($children) > 0;
            $hasAttributes = count($attributes) > 0;
            if ($hasChildren || $hasAttributes) {
                $node[] = $hasChildren ? $children : 0;
            }
            if ($hasAttributes) {
                $node[] = $attributes;
            }
        }
        return $node;
    }

    // getRaw V1 (using objects)
    public function getRaw(): array
    {
        $node = [];
        $node['c'] = $this->ItsExpression || $this->RawHtml || !$this->Content
            ? $this->Content
            : html_entity_decode($this->Content);
        $node['t'] = isset($this->Type) ? $this->Type->toShort() : 'r';
        if ($this->ItsExpression) {
            $node['e'] = 1;
        }
        if ($this->RawHtml) {
            $node['raw'] = 1;
        }
        if ($this->ItsExpression) {
            $node['code'] = $this->JsExpression;
            unset($node['c']);
            if ($this->Subscriptions != null) {
                $node['subs'] = $this->Subscriptions;
            }
            if (isset($this->DataExpression)) {
                if ($this->DataExpression->ForData !== null) {
                    $node['forData'] = $this->DataExpression->ForData;
                }
                if ($this->DataExpression->ForKey !== null) {
                    $node['forKey'] = $this->DataExpression->ForKey;
                }
                if ($this->DataExpression->ForItem !== null) {
                    $node['forItem'] = $this->DataExpression->ForItem;
                }
            }
        }
        if (isset($this->DynamicChild)) {
            $node['dynamic'] = $this->DynamicChild->getRaw();
        }
        if (isset($this->Children)) {
            foreach ($this->Children as &$child) {
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
                    $node['a'][] = $child->getRaw();
                } else {
                    if (!isset($node['h'])) {
                        $node['h'] = [];
                    }
                    $node['h'][] = $child->getRaw();
                }
            }
        }
        return $node;
    }

    public function &parent(): ?TagItem
    {
        return $this->Parent;
    }
    public function prependChild(TagItem $item): void
    {
        array_unshift($this->Children, $item);
    }

    public function setChildren(array $children): void
    {
        $this->Children = $children;
    }

    /**
     * 
     * @return null|TagItem[] 
     */
    public function &getChildren(): ?array
    {
        if (!isset($this->Children)) {
            $empty = [];
            return $empty;
        }
        return $this->Children;
    }

    public function &currentChild(): TagItem
    {
        return $this->Children[count($this->Children) - 1];
    }

    public function addChild(TagItem $child): void
    {
        if (!isset($this->Children)) {
            $this->Children = array();
        }
        $this->Children[] = $child;
    }

    public function &newChild(): TagItem
    {
        $child = new TagItem();
        $child->Parent = &$this;
        if (!isset($this->Children)) {
            $this->Children = array();
        }
        $this->Children[] = $child;
        return $this->Children[count($this->Children) - 1];
    }

    public function closeTag(): void
    {
        array_pop($this->Children);
    }

    public function cleanParents(): void
    {
        unset($this->Parent);
        if (isset($this->Children)) {
            foreach ($this->Children as &$child) {
                $child->cleanParents();
            }
        }
    }
}
