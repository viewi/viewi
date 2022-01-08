<?php

namespace Viewi\JsFunctions\Functions\Ctype;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class CtypeGraph extends BaseFunctionConverter
{
    public static string $name = 'ctype_graph';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $translator->includeFunction('setlocale');
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'CtypeGraph.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
