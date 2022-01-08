<?php

namespace Viewi\JsFunctions\Functions\Arrays;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Sizeof extends BaseFunctionConverter
{
    public static string $name = 'sizeof';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $translator->includeFunction('count');
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Sizeof.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
