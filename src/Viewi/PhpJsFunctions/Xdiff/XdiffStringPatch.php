<?php

namespace Viewi\PhpJsFunctions\Xdiff;

use Viewi\JsTranspile\BaseFunction;

class XdiffStringPatch extends BaseFunction
{
    public static string $name = 'xdiff_string_patch';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'XdiffStringPatch.js';
        return file_get_contents($jsToInclude);
    }
}
