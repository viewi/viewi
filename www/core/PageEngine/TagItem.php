<?php

class TagItem
{
    public ?string $Content = null;
    public TagItemType $Type;
    public bool $ItsExpression = false;
    private ?TagItem $Parent;

    /** @var TagItem[] */
    private ?array $childs;

    public function &parent(): ?TagItem
    {
        return $this->Parent;
    }
    public function prependChild(TagItem $item): void
    {
        array_unshift($this->childs, $item);
    }
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
