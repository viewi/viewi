<?php

namespace Viewi\JsFunctions\Functions\Strings;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Strstr extends BaseFunctionConverter
{
    public static string $name = 'strstr';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Strstr.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
