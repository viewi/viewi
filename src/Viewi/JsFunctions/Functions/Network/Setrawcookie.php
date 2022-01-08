<?php

namespace Viewi\JsFunctions\Functions\Network;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Setrawcookie extends BaseFunctionConverter
{
    public static string $name = 'setrawcookie';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Setrawcookie.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
