<?php
namespace Vo;
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
    const Attribute = 'Attribute';
    const AttributeValue = 'AttributeValue';
}
