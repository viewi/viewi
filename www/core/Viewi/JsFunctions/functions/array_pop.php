<?php

namespace Viewi;

class JsArrayPop extends BaseFunctionConverter
{
    public static string $name = 'array_pop';
    public static function Convert(
        JsTranslator $translator,
        string $code,
        string $identation
    ): string {
        $code = substr($code, 0, -9);

        $breakOn = new BreakCondition();
        $breakOn->Keyword = ')';
        $breakOn->ParenthesisNormal = 0;
        $code .= $translator->ReadCodeBlock($breakOn);
        $translator->SkipToTheSymbol(')');
        $code .= '.pop()';
        $translator->ActivateReactivity([$translator->latestVariablePath, "'pop'"]);

        return $code;
    }
}
