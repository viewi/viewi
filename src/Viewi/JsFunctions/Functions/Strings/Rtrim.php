<?php

namespace Viewi\JsFunctions\Functions\Strings;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Rtrim extends BaseFunctionConverter
{
    public static string $name = 'rtrim';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Rtrim.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
