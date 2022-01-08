<?php

namespace Viewi\JsFunctions\Functions\Arrays;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Shuffle extends BaseFunctionConverter
{
    public static string $name = 'shuffle';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Shuffle.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
