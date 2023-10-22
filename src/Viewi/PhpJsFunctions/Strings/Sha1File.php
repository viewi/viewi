<?php

namespace Viewi\PhpJsFunctions\Strings;

use Viewi\JsTranspile\BaseFunction;

class Sha1File extends BaseFunction
{
    public static string $name = 'sha1_file';

    public static function getUses(): array
    {
        return ['file_get_contents', 'sha1'];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Sha1File.js';
        return file_get_contents($jsToInclude);
    }
}
