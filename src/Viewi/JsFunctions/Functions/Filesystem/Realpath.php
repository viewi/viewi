<?php

namespace Viewi\JsFunctions\Functions\Filesystem;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Realpath extends BaseFunctionConverter
{
    public static string $name = 'realpath';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $translator->includeFunction('path');
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Realpath.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
