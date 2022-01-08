<?php

namespace Viewi\JsFunctions\Functions\Strings;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Chop extends BaseFunctionConverter
{
    public static string $name = 'chop';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $translator->includeFunction('rtrim');
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Chop.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
