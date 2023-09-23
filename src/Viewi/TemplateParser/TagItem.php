<?php

namespace Viewi\TemplateParser;

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
    public ?int $JsExpressionCode = null;
    public ?string $PhpExpression = null;
    public ?string $PropValueExpression = null;
    public ?array $Subscriptions = null;
    public bool $RawHtml = false;
    public bool $Used = false;
    public ?DataExpression $DataExpression = null;
    /** @var TagItem[] */
    private ?array $Children;
    public array $Slots;

    // TODO: get rid of Parent to avoid recursion
    public function &parent(): ?TagItem
    {
        return $this->Parent;
    }

    public function prependChild(TagItem $item): void
    {
        array_unshift($this->Children, $item);
    }

    public function addSlot(string $name, TagItem $item): void
    {
        if (!isset($this->Slots)) {
            $this->Slots = [];
        }
        $this->Slots[$name] = $item;
    }

    public function setChildren(array $children): void
    {
        $this->Children = $children;
    }

    /**
     * 
     * @return TagItem[] 
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
