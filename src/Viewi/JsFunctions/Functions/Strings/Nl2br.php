<?php

namespace Viewi\JsFunctions\Functions\Strings;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Nl2br extends BaseFunctionConverter
{
    public static string $name = 'nl2br';
    
    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclue = __DIR__ . DIRECTORY_SEPARATOR . 'Nl2br.js';
        $translator->includeJsFile(self::$name, $jsToInclue);
        return $code . '(';
    }
}
