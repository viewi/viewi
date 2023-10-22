<?php

namespace Viewi\PhpJsFunctions\Filesystem;

use Viewi\JsTranspile\BaseFunction;

class FileGetContents extends BaseFunction
{
    public static string $name = 'file_get_contents';

    public static function getUses(): array
    {
        return ['fs'];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'FileGetContents.js';
        return file_get_contents($jsToInclude);
    }
}
