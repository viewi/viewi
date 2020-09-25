<?php

namespace Vo;

class TagItem
{
    public ?string $Content = null;
    public TagItemType $Type;
    public bool $ItsExpression = false;
    public bool $Skip = false;
    private ?TagItem $Parent;
    public ?string $JsExpression = null;
    public ?array $Subscriptions = null;
    public bool $RawHtml = false;
    public ?DataExpression $DataExpression;
    /** @var TagItem[] */
    private ?array $childs;

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
        if (isset($this->childs)) {
            foreach ($this->childs as &$child) {
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
                    if (!isset($node['childs'])) {
                        $node['childs'] = [];
                    }
                    $node['childs'][] = $child->getRaw();
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
        array_unshift($this->childs, $item);
    }

    public function setChildren(array $children): void
    {
        $this->childs = $children;
    }

    /**
     * 
     * @return null|TagItem[] 
     */
    public function &getChildren(): ?array
    {
        if (!isset($this->childs)) {
            return [];
        }
        return $this->childs;
    }

    public function &currentChild(): TagItem
    {
        return $this->childs[count($this->childs) - 1];
    }

    public function addChild(TagItem $child): void
    {
        if (!isset($this->childs)) {
            $this->childs = array();
        }
        $this->childs[] = $child;
    }

    public function &newChild(): TagItem
    {
        $child = new TagItem();
        $child->Parent = &$this;
        if (!isset($this->childs)) {
            $this->childs = array();
        }
        $this->childs[] = $child;
        return $this->childs[count($this->childs) - 1];
    }

    public function closeTag(): void
    {
        array_pop($this->childs);
    }

    public function cleanParents(): void
    {
        unset($this->Parent);
        if (isset($this->childs)) {
            foreach ($this->childs as &$child) {
                $child->cleanParents();
            }
        }
    }
}
