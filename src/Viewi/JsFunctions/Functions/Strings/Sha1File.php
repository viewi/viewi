<?php

namespace Viewi\JsFunctions\Functions\Strings;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Sha1File extends BaseFunctionConverter
{
    public static string $name = 'sha1_file';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $translator->includeFunction('file_get_contents');
        $translator->includeFunction('sha1');
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Sha1File.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
