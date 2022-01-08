<?php

namespace Viewi\JsFunctions\Functions\Math;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class MtRand extends BaseFunctionConverter
{
    public static string $name = 'mt_rand';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'MtRand.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
