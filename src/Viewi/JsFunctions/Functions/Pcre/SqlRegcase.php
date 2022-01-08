<?php

namespace Viewi\JsFunctions\Functions\Pcre;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class SqlRegcase extends BaseFunctionConverter
{
    public static string $name = 'sql_regcase';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $translator->includeFunction('setlocale');
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'SqlRegcase.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
