<?php

namespace Viewi\JsFunctions\Functions\Strings;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Addcslashes extends BaseFunctionConverter
{
    public static string $name = 'addcslashes';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Addcslashes.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
