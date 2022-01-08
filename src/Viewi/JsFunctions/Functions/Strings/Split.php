<?php

namespace Viewi\JsFunctions\Functions\Strings;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Split extends BaseFunctionConverter
{
    public static string $name = 'split';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $translator->includeFunction('explode');
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Split.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
