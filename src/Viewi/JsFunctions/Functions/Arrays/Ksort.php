<?php

namespace Viewi\JsFunctions\Functions\Arrays;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Ksort extends BaseFunctionConverter
{
    public static string $name = 'ksort';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $translator->includeFunction('i18n_loc_get_default');
        $translator->includeFunction('strnatcmp');
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Ksort.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
