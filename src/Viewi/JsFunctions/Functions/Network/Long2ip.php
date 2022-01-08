<?php

namespace Viewi\JsFunctions\Functions\Network;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Long2ip extends BaseFunctionConverter
{
    public static string $name = 'long2ip';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Long2ip.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
