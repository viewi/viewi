<?php

namespace Viewi\JsFunctions\Functions\Strings;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Strtr extends BaseFunctionConverter
{
    public static string $name = 'strtr';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $translator->includeFunction('krsort');
        $translator->includeFunction('ini_set');
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Strtr.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
