<?php

namespace Vo;

class JsInArray extends BaseFunctionConverter
{
    public static string $name = 'in_array';
    public static function Convert(
        JsTranslator $translator,
        string $code,
        string $identation
    ): string {
        $jsToInclue = __DIR__ . DIRECTORY_SEPARATOR . 'in_array.js';
        $translator->IncludeJsFile(self::$name, $jsToInclue);
        return $code . '(';
    }
}
