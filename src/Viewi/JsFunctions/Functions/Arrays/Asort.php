<?php

namespace Viewi\JsFunctions\Functions\Arrays;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Asort extends BaseFunctionConverter
{
    public static string $name = 'asort';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $translator->includeFunction('strnatcmp');
        $translator->includeFunction('i18n_loc_get_default');
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Asort.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
