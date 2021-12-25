<?php

namespace Viewi\JsFunctions\Functions\Arrays;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class ArrayPop extends BaseFunctionConverter
{
    public static string $name = 'array_pop';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclue = __DIR__ . DIRECTORY_SEPARATOR . 'ArrayPop.js';
        $translator->includeJsFile(self::$name, $jsToInclue);
        $translator->activateReactivity([null, "'pop'"]);
        return $code . '(';
    }
}
