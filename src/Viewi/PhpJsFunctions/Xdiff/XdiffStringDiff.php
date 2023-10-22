<?php

namespace Viewi\PhpJsFunctions\Xdiff;

use Viewi\JsTranspile\BaseFunction;

class XdiffStringDiff extends BaseFunction
{
    public static string $name = 'xdiff_string_diff';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'XdiffStringDiff.js';
        return file_get_contents($jsToInclude);
    }
}
