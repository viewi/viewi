<?php

namespace Viewi\JsFunctions\Functions\Funchand;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class CreateFunction extends BaseFunctionConverter
{
    public static string $name = 'create_function';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'CreateFunction.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
