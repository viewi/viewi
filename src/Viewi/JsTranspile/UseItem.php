<?php

namespace Viewi\JsTranspile;

class UseItem
{
    const Class_ = 'CL';
    const Function = 'F';

    public function __construct(public array $Parts, public string $Type)
    {
    }
}