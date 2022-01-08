<?php

namespace Viewi\JsFunctions\Functions\Strings;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Strcmp extends BaseFunctionConverter
{
    public static string $name = 'strcmp';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Strcmp.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
