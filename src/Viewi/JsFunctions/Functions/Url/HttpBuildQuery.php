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
        string $indentation
    ): string {
        $translator->includeFunction('rawurlencode');
        $translator->includeFunction('urlencode');
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'HttpBuildQuery.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
