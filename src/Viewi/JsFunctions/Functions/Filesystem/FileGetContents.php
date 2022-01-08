<?php

namespace Viewi\JsFunctions\Functions\Filesystem;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class FileGetContents extends BaseFunctionConverter
{
    public static string $name = 'file_get_contents';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $translator->includeFunction('fs');
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'FileGetContents.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
