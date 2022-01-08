<?php

namespace Viewi\JsFunctions\Functions\Vars;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class VarDump extends BaseFunctionConverter
{
    public static string $name = 'var_dump';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'VarDump.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
