<?php

namespace Viewi\JsFunctions\Functions\Strings;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Strnatcmp extends BaseFunctionConverter
{
    public static string $name = 'strnatcmp';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $translator->includeFunction('_phpCastString');
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Strnatcmp.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
