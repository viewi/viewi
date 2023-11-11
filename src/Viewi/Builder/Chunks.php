<?php

namespace Viewi\Builder;

class Chunks
{
    /**
     * 
     * @var Chunk[]
     */
    public array $chunks = [];

    public function __construct()
    {
    }

    public function create(string $name, string $jsComponentsPath, string $jsFunctionsPath, string $jsResourcesPath): Chunk
    {
        $this->chunks[$name] = new Chunk($name, $jsComponentsPath, $jsFunctionsPath, $jsResourcesPath);
        return $this->chunks[$name];
    }
}
