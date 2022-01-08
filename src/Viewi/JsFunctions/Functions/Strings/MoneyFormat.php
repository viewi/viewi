<?php

namespace Viewi\JsFunctions\Functions\Strings;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class MoneyFormat extends BaseFunctionConverter
{
    public static string $name = 'money_format';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $translator->includeFunction('setlocale');
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'MoneyFormat.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
