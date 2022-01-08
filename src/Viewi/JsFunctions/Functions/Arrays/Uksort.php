<?php

namespace Viewi\JsFunctions\Functions\Arrays;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Uksort extends BaseFunctionConverter
{
    public static string $name = 'uksort';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Uksort.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
