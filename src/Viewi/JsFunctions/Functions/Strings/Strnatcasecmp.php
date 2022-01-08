<?php

namespace Viewi\JsFunctions\Functions\Strings;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Strnatcasecmp extends BaseFunctionConverter
{
    public static string $name = 'strnatcasecmp';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $translator->includeFunction('strnatcmp');
        $translator->includeFunction('_phpCastString');
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Strnatcasecmp.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
