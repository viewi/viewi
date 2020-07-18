<?php

namespace Vo;

class JsEcho extends BaseFunctionConverter
{
    public static bool $directive = true;
    public static string $name = 'echo';
    public static function Convert(
        JsTranslator $translator,
        string $code,
        string $identation
    ): string {
        $code = substr($code, 0, -4);
        $code .= $identation . 'console.log(';
        $code .= $translator->ReadCodeBlock(';');
        // $translator->SkipToTheSymbol(';');
        $code .= ')';

        return $code;
    }
}
