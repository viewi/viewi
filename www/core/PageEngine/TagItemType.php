<?php

class TagItemType
{
    public string $Type;
    public function __construct(string $type)
    {
        $this->Type = $type;
    }
    const Tag = 'Tag';
    const TextContent = 'TextContent';
    const Component = 'Component';
    const Expression = 'Expression';
}
