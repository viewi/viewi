<?php

namespace Viewi\JsFunctions\Functions\Strings;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Ucfirst extends BaseFunctionConverter
{
    public static string $name = 'ucfirst';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Ucfirst.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
