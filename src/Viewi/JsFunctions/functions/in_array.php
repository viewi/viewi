<?php

namespace Viewi;

class JsInArray extends BaseFunctionConverter
{
    public static string $name = 'in_array';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $identation
    ): string {
        $jsToInclue = __DIR__ . DIRECTORY_SEPARATOR . 'in_array.js';
        $translator->includeJsFile(self::$name, $jsToInclue);
        return $code . '(';
    }
}
