<?php

namespace Viewi\JsFunctions\Functions\Bc;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Bcmul extends BaseFunctionConverter
{
    public static string $name = 'bcmul';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $translator->includeFunction('_bc');
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Bcmul.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
