<?php

namespace Viewi\JsFunctions\Functions\Math;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Decoct extends BaseFunctionConverter
{
    public static string $name = 'decoct';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Decoct.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
