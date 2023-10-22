<?php

namespace Viewi\PhpJsFunctions\Strings;

use Viewi\JsTranspile\BaseFunction;

class Sha1 extends BaseFunction
{
    public static string $name = 'sha1';

    public static function getUses(): array
    {
        return ['crypto'];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Sha1.js';
        return file_get_contents($jsToInclude);
    }
}
