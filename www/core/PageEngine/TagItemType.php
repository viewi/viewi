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
    const Comment = 'Comment';

    public static $shorts = [
        self::Attribute => 'attr',
        self::AttributeValue => 'value',
        self::Component => 'component',
        self::Tag => 'tag',
        self::TextContent => 'text',
        self::Comment => 'comment'
    ];
    public function toShort(): string
    {
        return self::$shorts[$this->Name];
    }
}
