<?php

namespace Viewi\JsFunctions\Functions\Strings;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Lcfirst extends BaseFunctionConverter
{
    public static string $name = 'lcfirst';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Lcfirst.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
