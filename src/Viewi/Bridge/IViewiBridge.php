<?php

namespace Viewi\Bridge;

use Viewi\Components\Http\Message\Request;
use Viewi\Engine;

interface IViewiBridge
{
    function file_exists(string $filename): bool;
    function is_dir(string $filename): bool;
    function file_get_contents(string $filename): string | false;
    function request(Request $request, Engine $currentEngine): mixed;
}
