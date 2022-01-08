<?php

namespace Viewi\JsFunctions\Functions\Strings;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Strcspn extends BaseFunctionConverter
{
    public static string $name = 'strcspn';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Strcspn.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
