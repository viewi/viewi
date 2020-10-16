<?php

namespace Viewi;

class JsCount extends BaseFunctionConverter
{
    public static string $name = 'count';
    public static function Convert(
        JsTranslator $translator,
        string $code,
        string $identation
    ): string {
        $code = substr($code, 0, -5);

        $breakOn = new BreakCondition();
        $breakOn->Keyword = ')';
        $breakOn->ParenthesisNormal = 0;
        $code .= $translator->ReadCodeBlock($breakOn);
        $translator->SkipToTheSymbol(')');
        $code .= '.length';
        return $code;
    }
}
