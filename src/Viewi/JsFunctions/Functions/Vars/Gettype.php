<?php

namespace Viewi\JsFunctions\Functions\Vars;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Gettype extends BaseFunctionConverter
{
    public static string $name = 'gettype';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $translator->includeFunction('is_float');
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Gettype.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
