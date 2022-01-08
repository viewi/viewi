<?php

namespace Viewi\JsFunctions\Functions\Strings;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class StripTags extends BaseFunctionConverter
{
    public static string $name = 'strip_tags';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $translator->includeFunction('_phpCastString');
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'StripTags.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
