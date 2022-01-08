<?php

namespace Viewi\JsFunctions\Functions\I18n;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class I18nLocSetDefault extends BaseFunctionConverter
{
    public static string $name = 'i18n_loc_set_default';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'I18nLocSetDefault.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
