<?php

namespace Viewi\Components\Services;

class HashService
{
    function hashCode(string $str): int
    {
        $str = $str;
        $hash = 0;
        $len = strlen($str);
        if ($len == 0)
            return $hash;

        for ($i = 0; $i < $len; $i++) {
            $h = $hash << 5;
            $h -= $hash;
            $h += ord($str[$i]);
            $hash = $h;
            $hash = $hash & 0xFFFFFFFF;
        }
        return $hash;
    }
}
