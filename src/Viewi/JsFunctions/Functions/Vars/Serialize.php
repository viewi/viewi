<?php

namespace Viewi\JsFunctions\Functions\Vars;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Serialize extends BaseFunctionConverter
{
    public static string $name = 'serialize';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Serialize.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
