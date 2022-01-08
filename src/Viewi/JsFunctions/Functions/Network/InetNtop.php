<?php

namespace Viewi\JsFunctions\Functions\Network;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class InetNtop extends BaseFunctionConverter
{
    public static string $name = 'inet_ntop';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'InetNtop.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
