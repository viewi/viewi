<?php

namespace Viewi\JsFunctions\Functions\Strings;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Strpbrk extends BaseFunctionConverter
{
    public static string $name = 'strpbrk';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Strpbrk.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
