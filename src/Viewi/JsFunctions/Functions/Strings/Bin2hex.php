<?php

namespace Viewi\JsFunctions\Functions\Strings;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Bin2hex extends BaseFunctionConverter
{
    public static string $name = 'bin2hex';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Bin2hex.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
