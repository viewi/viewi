<?php

namespace Viewi\JsFunctions\Functions\Vars;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Strval extends BaseFunctionConverter
{
    public static string $name = 'strval';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $translator->includeFunction('gettype');
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Strval.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
