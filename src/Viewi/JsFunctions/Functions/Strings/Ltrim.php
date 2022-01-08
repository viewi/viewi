<?php

namespace Viewi\JsFunctions\Functions\Strings;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Ltrim extends BaseFunctionConverter
{
    public static string $name = 'ltrim';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Ltrim.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
