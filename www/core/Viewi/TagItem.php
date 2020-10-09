<?php

namespace Viewi;

class TagItem
{
    public ?string $Content = null;
    public TagItemType $Type;
    public bool $ItsExpression = false;
    public bool $Skip = false;
    private ?TagItem $Parent;
    public ?TagItem $dynamicChild;
    public ?string $JsExpression = null;
    public ?string $PhpExpression = null;
    public ?array $Subscriptions = null;
    public bool $RawHtml = false;
    public ?DataExpression $DataExpression;
    /** @var TagItem[] */
    private ?array $children;

    public function getRaw(): array
    {
        $node = [];

        $node['content'] = $this->Content;
        $node['type'] = isset($this->Type) ? $this->Type->toShort() : 'root';
        $node['expression'] = $this->ItsExpression;
        if ($this->ItsExpression) {
            $node['code'] = $this->JsExpression;
            unset($node['content']);
            if ($this->Subscriptions != null) {
                $node['subs'] = $this->Subscriptions;
            }
            if ($this->RawHtml) {
                $node['raw'] = true;
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
        if (isset($this->dynamicChild)) {
            $node['dynamic'] = $this->dynamicChild->getRaw();
        }
        if (isset($this->children)) {
            foreach ($this->children as &$child) {
                if (
                    $child->Type->Name === TagItemType::TextContent
                    && $child->Skip
                ) {
                    continue;
                }
                if ($child->Type->Name === TagItemType::Attribute) {
                    if (!isset($node['attributes'])) {
                        $node['attributes'] = [];
                    }
                    $node['attributes'][] = $child->getRaw();
                } else {
                    if (!isset($node['children'])) {
                        $node['children'] = [];
                    }
                    $node['children'][] = $child->getRaw();
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
        array_unshift($this->children, $item);
    }

    public function setChildren(array $children): void
    {
        $this->children = $children;
    }

    /**
     * 
     * @return null|TagItem[] 
     */
    public function &getChildren(): ?array
    {
        if (!isset($this->children)) {
            return [];
        }
        return $this->children;
    }

    public function &currentChild(): TagItem
    {
        return $this->children[count($this->children) - 1];
    }

    public function addChild(TagItem $child): void
    {
        if (!isset($this->children)) {
            $this->children = array();
        }
        $this->children[] = $child;
    }

    public function &newChild(): TagItem
    {
        $child = new TagItem();
        $child->Parent = &$this;
        if (!isset($this->children)) {
            $this->children = array();
        }
        $this->children[] = $child;
        return $this->children[count($this->children) - 1];
    }

    public function closeTag(): void
    {
        array_pop($this->children);
    }

    public function cleanParents(): void
    {
        unset($this->Parent);
        if (isset($this->children)) {
            foreach ($this->children as &$child) {
                $child->cleanParents();
            }
        }
    }
}
