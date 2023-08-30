<?php

namespace Viewi\TemplateParser;

class TagItemType
{
    const Tag = 'Tag';
    const Root = 'Root';
    const TextContent = 'TextContent';
    const Component = 'Component';
    const Attribute = 'Attribute';
    const AttributeValue = 'AttributeValue';
    const Comment = 'Comment';

    public static $shorts = [
        self::Attribute => 'a',
        self::AttributeValue => 'v',
        self::Component => 'c',
        self::Tag => 't',
        self::TextContent => 'x',
        self::Comment => 'm',
        self::Root => 'r'
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
