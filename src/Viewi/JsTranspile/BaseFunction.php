<?php

namespace Viewi\JsTranspile;

abstract class BaseFunction
{
    public static bool $directive = false;
    public static string $name = '__FUNCTION_NAME__';
    public abstract static function getUses(): array;
    public abstract static function getJs(): string;
}
