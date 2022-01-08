<?php

namespace Viewi\JsFunctions\Functions\Strings;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Sha1 extends BaseFunctionConverter
{
    public static string $name = 'sha1';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $translator->includeFunction('crypto');
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Sha1.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
