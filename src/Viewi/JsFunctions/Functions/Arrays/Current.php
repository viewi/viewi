<?php

namespace Viewi\JsFunctions\Functions\Arrays;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Current extends BaseFunctionConverter
{
    public static string $name = 'current';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Current.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
