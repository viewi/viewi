<?php

namespace Viewi\JsFunctions\Functions\Math;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Octdec extends BaseFunctionConverter
{
    public static string $name = 'octdec';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Octdec.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
