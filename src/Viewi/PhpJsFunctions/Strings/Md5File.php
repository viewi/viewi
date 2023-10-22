<?php

namespace Viewi\PhpJsFunctions\Strings;

use Viewi\JsTranspile\BaseFunction;

class Md5File extends BaseFunction
{
    public static string $name = 'md5_file';

    public static function getUses(): array
    {
        return ['file_get_contents', 'md5'];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Md5File.js';
        return file_get_contents($jsToInclude);
    }
}
