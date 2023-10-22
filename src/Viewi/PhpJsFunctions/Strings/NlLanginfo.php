<?php

namespace Viewi\PhpJsFunctions\Strings;

use Viewi\JsTranspile\BaseFunction;

class NlLanginfo extends BaseFunction
{
    public static string $name = 'nl_langinfo';

    public static function getUses(): array
    {
        return ['setlocale'];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'NlLanginfo.js';
        return file_get_contents($jsToInclude);
    }
}
