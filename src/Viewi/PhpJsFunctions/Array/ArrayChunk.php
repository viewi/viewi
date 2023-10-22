<?php

namespace Viewi\PhpJsFunctions\Array;

use Viewi\JsTranspile\BaseFunction;

class ArrayChunk extends BaseFunction
{
    public static string $name = 'array_chunk';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'ArrayChunk.js';
        return file_get_contents($jsToInclude);
    }
}
