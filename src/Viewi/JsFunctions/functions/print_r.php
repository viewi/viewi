<?php

namespace Viewi;

class JsPrintR extends BaseFunctionConverter
{
    public static string $name = 'print_r';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $identation
    ): string {
        $code = substr($code, 0, -7);
        $code .= 'console.log(';
        $code .= $translator->readCodeBlock(')');
        $translator->skipToTheSymbol(')');
        $code .= ')';

        return $code;
    }
}
