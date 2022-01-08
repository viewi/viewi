<?php

namespace Viewi\JsFunctions\Functions\Misc;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Pack extends BaseFunctionConverter
{
    public static string $name = 'pack';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Pack.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
