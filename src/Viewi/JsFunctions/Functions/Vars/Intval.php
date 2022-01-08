<?php

namespace Viewi\JsFunctions\Functions\Vars;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Intval extends BaseFunctionConverter
{
    public static string $name = 'intval';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Intval.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
