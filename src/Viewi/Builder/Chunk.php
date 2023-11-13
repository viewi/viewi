<?php

namespace Viewi\Builder;

class Chunk
{
    public const MAIN = 'main';

    public array $publicJSON = [];
    public string $componentsIndex = '';
    public string $componentsExport = '';
    public array $functions = [];
    public string $distFileName = '';
    public string $distFileMinName = '';
    public string $distFileJsonName = '';

    public function __construct(public string $name, public string $jsComponentsPath, public string $jsFunctionsPath, public string $jsResourcesPath)
    {
    }

    public function addComponent(string $componentName)
    {
        $this->publicJSON[$componentName] = [];
    }
}
