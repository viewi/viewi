<?php

namespace Viewi\JsFunctions\Functions\Strings;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Strcoll extends BaseFunctionConverter
{
    public static string $name = 'strcoll';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $translator->includeFunction('setlocale');
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Strcoll.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
