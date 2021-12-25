<?php

namespace Viewi\JsFunctions\Functions\Info;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class AssertOptions extends BaseFunctionConverter
{
    public static string $name = 'assert_options';
    
    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclue = __DIR__ . DIRECTORY_SEPARATOR . 'AssertOptions.js';
        $translator->includeJsFile(self::$name, $jsToInclue);
        return $code . '(';
    }
}
