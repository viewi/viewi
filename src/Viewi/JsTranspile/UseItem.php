<?php

namespace Viewi\JsTranspile;

class UseItem
{
    const Class_ = 'CL';
    const Function = 'F';
    const System = 'S';
    public bool $Skip = false;

    /**
     * @param bool $Internal a helper call the transpiler emitted itself (concatenation → _phpCastString,
     *   <=> → _php_compare): shipped like any function, but exempt from RestrictedFunctions
     */
    public function __construct(public array $Parts, public string $Type, public bool $Internal = false)
    {
    }
}
