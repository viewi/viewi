<?php

namespace Viewi\JsFunctions\Functions;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class JsImplode extends BaseFunctionConverter
{
    public static string $name = 'implode';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'JsImplode.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
