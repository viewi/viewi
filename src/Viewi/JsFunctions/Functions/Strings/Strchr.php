<?php

namespace Viewi\JsFunctions\Functions\Strings;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Strchr extends BaseFunctionConverter
{
    public static string $name = 'strchr';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $translator->includeFunction('strstr');
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Strchr.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
