<?php

namespace Viewi\JsFunctions;

use Viewi\JsTranslator;

abstract class BaseFunctionConverter
{
    public static bool $directive = false;

    public static string $name = '__FUNCTION_NAME__';

    public abstract static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string;
}
