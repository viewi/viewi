<?php

namespace Viewi\JsFunctions\Functions\Vars;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class IssetPHP extends BaseFunctionConverter
{
    public static string $name = 'isset';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'IssetPHP.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
