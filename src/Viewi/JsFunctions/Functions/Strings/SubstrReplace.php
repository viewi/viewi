<?php

namespace Viewi\JsFunctions\Functions\Strings;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class SubstrReplace extends BaseFunctionConverter
{
    public static string $name = 'substr_replace';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'SubstrReplace.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
