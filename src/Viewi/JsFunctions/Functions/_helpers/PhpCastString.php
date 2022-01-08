<?php

namespace Viewi\JsFunctions\Functions\_helpers;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class PhpCastString extends BaseFunctionConverter
{
    public static string $name = '_phpCastString';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'PhpCastString.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
