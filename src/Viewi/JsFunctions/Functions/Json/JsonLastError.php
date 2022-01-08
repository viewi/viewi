<?php

namespace Viewi\JsFunctions\Functions\Json;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class JsonLastError extends BaseFunctionConverter
{
    public static string $name = 'json_last_error';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'JsonLastError.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
