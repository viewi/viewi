<?php

namespace Viewi\PhpJsFunctions\I18n;

use Viewi\JsTranspile\BaseFunction;

class I18nLocGetDefault extends BaseFunction
{
    public static string $name = 'i18n_loc_get_default';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'I18nLocGetDefault.js';
        return file_get_contents($jsToInclude);
    }
}
