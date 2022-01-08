<?php

namespace Viewi\JsFunctions\Functions\Math;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Rand extends BaseFunctionConverter
{
    public static string $name = 'rand';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Rand.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
