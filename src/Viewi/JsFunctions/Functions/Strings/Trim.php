<?php

namespace Viewi\JsFunctions\Functions\Strings;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Trim extends BaseFunctionConverter
{
    public static string $name = 'trim';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Trim.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
