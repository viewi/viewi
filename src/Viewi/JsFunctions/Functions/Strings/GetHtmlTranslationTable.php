<?php

namespace Viewi\JsFunctions\Functions\Strings;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class GetHtmlTranslationTable extends BaseFunctionConverter
{
    public static string $name = 'get_html_translation_table';
    
    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclue = __DIR__ . DIRECTORY_SEPARATOR . 'GetHtmlTranslationTable.js';
        $translator->includeJsFile(self::$name, $jsToInclue);
        return $code . '(';
    }
}
