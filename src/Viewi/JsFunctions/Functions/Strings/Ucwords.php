<?php

namespace Viewi\JsFunctions\Functions\Strings;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Ucwords extends BaseFunctionConverter
{
    public static string $name = 'ucwords';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Ucwords.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
