<?php

namespace Viewi\JsFunctions\Functions\Vars;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class EmptyPHP extends BaseFunctionConverter
{
    public static string $name = 'empty';
    
    public static function convert(
        JsTranslator $translator,
        string $code,
        string $identation
    ): string {
        $jsToInclue = __DIR__ . DIRECTORY_SEPARATOR . 'EmptyPHP.js';
        $translator->includeJsFile(self::$name, $jsToInclue);
        return $code . '(';
    }
}
