<?php

namespace Viewi\JsFunctions\Functions\Network;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Setcookie extends BaseFunctionConverter
{
    public static string $name = 'setcookie';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $translator->includeFunction('setrawcookie');
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Setcookie.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
