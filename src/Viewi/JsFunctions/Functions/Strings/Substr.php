<?php

namespace Viewi\JsFunctions\Functions\Strings;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Substr extends BaseFunctionConverter
{
    public static string $name = 'substr';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $translator->includeFunction('_phpCastString');
        $translator->includeFunction('ini_get');
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Substr.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
