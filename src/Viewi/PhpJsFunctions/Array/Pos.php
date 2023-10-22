<?php

namespace Viewi\PhpJsFunctions\Array;

use Viewi\JsTranspile\BaseFunction;

class Pos extends BaseFunction
{
    public static string $name = 'pos';

    public static function getUses(): array
    {
        return ['current'];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Pos.js';
        return file_get_contents($jsToInclude);
    }
}
