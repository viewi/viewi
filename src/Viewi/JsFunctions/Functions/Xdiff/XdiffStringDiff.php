<?php

namespace Viewi\JsFunctions\Functions\Xdiff;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class XdiffStringDiff extends BaseFunctionConverter
{
    public static string $name = 'xdiff_string_diff';
    
    public static function convert(
        JsTranslator $translator,
        string $code,
        string $identation
    ): string {
        $jsToInclue = __DIR__ . DIRECTORY_SEPARATOR . 'XdiffStringDiff.js';
        $translator->includeJsFile(self::$name, $jsToInclue);
        return $code . '(';
    }
}
