<?php

namespace Viewi;

class TagItemType
{
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

    public string $Name;

    public function __construct(string $type)
    {
        $this->Name = $type;
    }

    public function toShort(): string
    {
        return self::$shorts[$this->Name];
    }
}
