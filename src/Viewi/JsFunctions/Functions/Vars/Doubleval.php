<?php

namespace Viewi\JsFunctions\Functions\Vars;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Doubleval extends BaseFunctionConverter
{
    public static string $name = 'doubleval';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $translator->includeFunction('floatval');
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Doubleval.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
