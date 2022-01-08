<?php

namespace Viewi\JsFunctions\Functions\Bc;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Bcround extends BaseFunctionConverter
{
    public static string $name = 'bcround';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $translator->includeFunction('_bc');
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Bcround.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
