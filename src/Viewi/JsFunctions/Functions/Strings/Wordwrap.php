<?php

namespace Viewi\JsFunctions\Functions\Strings;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Wordwrap extends BaseFunctionConverter
{
    public static string $name = 'wordwrap';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Wordwrap.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
