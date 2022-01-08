<?php

namespace Viewi\JsFunctions\Functions\Filesystem;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Basename extends BaseFunctionConverter
{
    public static string $name = 'basename';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Basename.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
