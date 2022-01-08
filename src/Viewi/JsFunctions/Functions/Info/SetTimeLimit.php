<?php

namespace Viewi\JsFunctions\Functions\Info;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class SetTimeLimit extends BaseFunctionConverter
{
    public static string $name = 'set_time_limit';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'SetTimeLimit.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
