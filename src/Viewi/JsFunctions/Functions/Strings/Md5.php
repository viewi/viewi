<?php

namespace Viewi\JsFunctions\Functions\Strings;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Md5 extends BaseFunctionConverter
{
    public static string $name = 'md5';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $translator->includeFunction('crypto');
        $translator->includeFunction('utf8_encode');
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Md5.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
