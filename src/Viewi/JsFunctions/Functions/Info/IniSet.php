<?php

namespace Viewi\JsFunctions\Functions\Info;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class IniSet extends BaseFunctionConverter
{
    public static string $name = 'ini_set';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'IniSet.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
