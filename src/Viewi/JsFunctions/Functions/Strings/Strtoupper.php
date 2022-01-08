<?php

namespace Viewi\JsFunctions\Functions\Strings;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Strtoupper extends BaseFunctionConverter
{
    public static string $name = 'strtoupper';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Strtoupper.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
