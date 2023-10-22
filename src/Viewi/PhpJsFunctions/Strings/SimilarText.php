<?php

namespace Viewi\PhpJsFunctions\Strings;

use Viewi\JsTranspile\BaseFunction;

class SimilarText extends BaseFunction
{
    public static string $name = 'similar_text';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'SimilarText.js';
        return file_get_contents($jsToInclude);
    }
}
