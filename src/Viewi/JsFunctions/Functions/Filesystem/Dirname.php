<?php

namespace Viewi\JsFunctions\Functions\Filesystem;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Dirname extends BaseFunctionConverter
{
    public static string $name = 'dirname';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Dirname.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
