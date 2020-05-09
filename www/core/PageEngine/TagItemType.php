<?php

class TagItemType
{
    public string $Name;
    public function __construct(string $type)
    {
        $this->Name = $type;
    }
    const Tag = 'Tag';
    const TextContent = 'TextContent';
    const Component = 'Component';
    const Expression = 'Expression';
    const Attribute = 'Attribute';
}
