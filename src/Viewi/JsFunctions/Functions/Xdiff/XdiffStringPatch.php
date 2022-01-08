<?php

namespace Viewi\JsFunctions\Functions\Xdiff;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class XdiffStringPatch extends BaseFunctionConverter
{
    public static string $name = 'xdiff_string_patch';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'XdiffStringPatch.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
