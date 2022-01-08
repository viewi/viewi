<?php

namespace Viewi\JsFunctions\Functions\Strings;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Strncasecmp extends BaseFunctionConverter
{
    public static string $name = 'strncasecmp';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Strncasecmp.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
