<?php

namespace Viewi\PhpJsFunctions\Strings;

use Viewi\JsTranspile\BaseFunction;

class StripTags extends BaseFunction
{
    public static string $name = 'strip_tags';

    public static function getUses(): array
    {
        return ['_phpCastString'];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'StripTags.js';
        return file_get_contents($jsToInclude);
    }
}
