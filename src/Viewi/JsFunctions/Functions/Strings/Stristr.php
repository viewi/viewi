<?php

namespace Viewi\JsFunctions\Functions\Strings;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Stristr extends BaseFunctionConverter
{
    public static string $name = 'stristr';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Stristr.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
