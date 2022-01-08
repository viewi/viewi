<?php

namespace Viewi\JsFunctions\Functions\Strings;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class SimilarText extends BaseFunctionConverter
{
    public static string $name = 'similar_text';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'SimilarText.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
