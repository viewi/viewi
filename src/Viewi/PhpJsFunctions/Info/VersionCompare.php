<?php

namespace Viewi\PhpJsFunctions\Info;

use Viewi\JsTranspile\BaseFunction;

class VersionCompare extends BaseFunction
{
    public static string $name = 'version_compare';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'VersionCompare.js';
        return file_get_contents($jsToInclude);
    }
}
