<?php

namespace Viewi\JsFunctions\Functions\Info;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class VersionCompare extends BaseFunctionConverter
{
    public static string $name = 'version_compare';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'VersionCompare.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
