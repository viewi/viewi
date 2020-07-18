<?php

namespace Vo;

class JsCount extends BaseFunctionConverter
{
    public static string $name = 'count';
    public static function Convert(
        JsTranslator $translator,
        string $code,
        string $identation
    ): string {
        $code = substr($code, 0, -5);

        $code .= $translator->ReadCodeBlock(')');
        $translator->SkipToTheSymbol(')');
        $code .= '.length';

        return $code;
    }
}
