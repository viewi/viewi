<?php

namespace Vo;

class JsPrintR extends BaseFunctionConverter
{
    public static string $name = 'print_r';
    public static function Convert(
        JsTranslator $translator,
        string $code,
        string $identation
    ): string {
        $code = substr($code, 0, -7);
        $code .= 'console.log(';
        $code .= $translator->ReadCodeBlock(')');
        $translator->SkipToTheSymbol(')');
        $code .= ')';

        return $code;
    }
}
