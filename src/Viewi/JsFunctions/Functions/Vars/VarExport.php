<?php

namespace Viewi\JsFunctions\Functions\Vars;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class VarExport extends BaseFunctionConverter
{
    public static string $name = 'var_export';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'VarExport.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
