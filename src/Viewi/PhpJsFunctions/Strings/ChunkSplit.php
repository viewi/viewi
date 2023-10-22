<?php

namespace Viewi\PhpJsFunctions\Strings;

use Viewi\JsTranspile\BaseFunction;

class ChunkSplit extends BaseFunction
{
    public static string $name = 'chunk_split';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'ChunkSplit.js';
        return file_get_contents($jsToInclude);
    }
}
