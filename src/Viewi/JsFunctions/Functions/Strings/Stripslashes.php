<?php

namespace Viewi\JsFunctions\Functions\Strings;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Stripslashes extends BaseFunctionConverter
{
    public static string $name = 'stripslashes';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Stripslashes.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
