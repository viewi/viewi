<?php

namespace Viewi\JsFunctions\Functions\Strings;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Addslashes extends BaseFunctionConverter
{
    public static string $name = 'addslashes';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Addslashes.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
