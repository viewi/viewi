<?php

namespace Viewi\JsFunctions\Functions\Strings;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Strspn extends BaseFunctionConverter
{
    public static string $name = 'strspn';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Strspn.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
