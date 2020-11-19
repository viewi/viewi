<?php

namespace Viewi\JsFunctions\Functions\Url;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class HttpBuildQuery extends BaseFunctionConverter
{
    public static string $name = 'http_build_query';
    
    public static function convert(
        JsTranslator $translator,
        string $code,
        string $identation
    ): string {
        $jsToInclue = __DIR__ . DIRECTORY_SEPARATOR . 'HttpBuildQuery.js';
        $translator->includeJsFile(self::$name, $jsToInclue);
        return $code . '(';
    }
}
