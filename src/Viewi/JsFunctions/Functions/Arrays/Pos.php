<?php

namespace Viewi\JsFunctions\Functions\Arrays;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Pos extends BaseFunctionConverter
{
    public static string $name = 'pos';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $translator->includeFunction('current');
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Pos.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
