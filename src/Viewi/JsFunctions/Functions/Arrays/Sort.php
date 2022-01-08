<?php

namespace Viewi\JsFunctions\Functions\Arrays;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Sort extends BaseFunctionConverter
{
    public static string $name = 'sort';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $translator->includeFunction('i18n_loc_get_default');
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Sort.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
