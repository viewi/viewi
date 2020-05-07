<?php

class TagItem
{
    public string $Name;
    public string $Content;
    public TagItemType $type;
    public bool $IsText;
    private ?TagItem $Parent;

    /** @var TagItem[] */
    private array $childs = [];

    public function &parent(): TagItem
    {
        return $this->Parent;
    }

    public function &currentChild(): TagItem
    {
        return $this->childs[count($this->childs) - 1];
    }

    public function &newChild(): TagItem
    {
        $child = new TagItem();
        $child->Parent = &$this;
        $this->childs[] = $child;
        return $this->childs[count($this->childs) - 1];
    }

    public function closeTag(): void
    {
        array_pop($this->childs);
    }

    public function cleanParents(): void
    {
        $this->Parent = null;
        foreach ($this->childs as &$child) {
            $child->cleanParents();
        }
    }
}
