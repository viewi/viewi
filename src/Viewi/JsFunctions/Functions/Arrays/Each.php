<?php

namespace Viewi\JsFunctions\Functions\Arrays;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Each extends BaseFunctionConverter
{
    public static string $name = 'each';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Each.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
