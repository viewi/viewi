<?php

namespace Viewi\JsFunctions\Functions\Arrays;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Next extends BaseFunctionConverter
{
    public static string $name = 'next';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Next.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
