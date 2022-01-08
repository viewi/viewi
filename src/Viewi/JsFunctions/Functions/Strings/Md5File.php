<?php

namespace Viewi\JsFunctions\Functions\Strings;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Md5File extends BaseFunctionConverter
{
    public static string $name = 'md5_file';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $translator->includeFunction('file_get_contents');
        $translator->includeFunction('md5');
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Md5File.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
