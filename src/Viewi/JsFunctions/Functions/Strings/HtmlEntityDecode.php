<?php

namespace Viewi\JsFunctions\Functions\Strings;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class HtmlEntityDecode extends BaseFunctionConverter
{
    public static string $name = 'html_entity_decode';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $translator->includeFunction('get_html_translation_table');
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'HtmlEntityDecode.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
