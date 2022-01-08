<?php

namespace Viewi\JsFunctions\Functions\Strings;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Quotemeta extends BaseFunctionConverter
{
    public static string $name = 'quotemeta';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Quotemeta.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
