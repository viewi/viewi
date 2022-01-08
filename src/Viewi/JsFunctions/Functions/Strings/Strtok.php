<?php

namespace Viewi\JsFunctions\Functions\Strings;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Strtok extends BaseFunctionConverter
{
    public static string $name = 'strtok';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Strtok.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
