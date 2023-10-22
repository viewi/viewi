<?php

namespace Viewi\PhpJsFunctions\Strings;

use Viewi\JsTranspile\BaseFunction;

class Nl2br extends BaseFunction
{
    public static string $name = 'nl2br';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Nl2br.js';
        return file_get_contents($jsToInclude);
    }
}
