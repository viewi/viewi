<?php

namespace Viewi;

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

    public function getRaw(): array
    {
        $node = [];

        $node['content'] = $this->Content;
        $node['type'] = isset($this->Type) ? $this->Type->toShort() : 'root';
        $node['expression'] = $this->ItsExpression;
        if ($this->RawHtml) {
            $node['raw'] = true;
        }
        if ($this->ItsExpression) {
            $node['code'] = $this->JsExpression;
            unset($node['content']);
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
