<?php

namespace Viewi;

class Helper
{
    public static function concatPath(string ...$paths): string
    {
        $newPath = '';
        foreach ($paths as $path) {
            $newPath = substr($newPath, -1, 1) == '/' || $newPath == ''
                ? $newPath . $path
                : "$newPath/$path";
        }

        return $newPath;
    }
}