<?php

namespace Viewi\JsFunctions\Functions\Arrays;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Natsort extends BaseFunctionConverter
{
    public static string $name = 'natsort';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $translator->includeFunction('strnatcmp');
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Natsort.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
