<?php

namespace Viewi\JsFunctions\Functions\Arrays;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Natcasesort extends BaseFunctionConverter
{
    public static string $name = 'natcasesort';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $translator->includeFunction('strnatcasecmp');
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Natcasesort.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
