<?php

namespace Viewi\JsFunctions\Functions\Vars;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class IsCallable extends BaseFunctionConverter
{
    public static string $name = 'is_callable';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'IsCallable.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
