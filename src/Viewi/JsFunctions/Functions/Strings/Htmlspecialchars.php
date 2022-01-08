<?php

namespace Viewi\JsFunctions\Functions\Strings;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Htmlspecialchars extends BaseFunctionConverter
{
    public static string $name = 'htmlspecialchars';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Htmlspecialchars.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
