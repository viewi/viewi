<?php

namespace Viewi\JsTranspile;

class ExportItem
{
    const Namespace = 'NS';
    const Class_ = 'CL';
    const Method = 'M';
    const Property = 'P';
    const Function = 'F';

    /**
     * 
     * @var array<string, ExportItem>
     */
    public array $Children = [];
    public ?array $Attributes = null;

    public function __construct(public string $Type, public string $Name)
    {
    }

    public static function NewNamespace(string $name): self
    {
        return new self(self::Namespace, $name);
    }

    public static function NewClass(string $name, string $namespace): self
    {
        $item = new self(self::Class_, $name);
        $item->Attributes['namespace'] = $namespace;
        return $item;
    }

    public static function NewMethod(string $name): self
    {
        return new self(self::Method, $name);
    }

    public static function NewProperty(string $name): self
    {
        return new self(self::Property, $name);
    }

    public static function NewFunction(string $name): self
    {
        return new self(self::Function, $name);
    }
}
