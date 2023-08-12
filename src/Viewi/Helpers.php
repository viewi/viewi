<?php

namespace Viewi;

class Helpers
{
    /**
     * 
     * @param mixed $dir 
     * @param array $results 
     * @param bool $includeFolders 
     * @return array<string, string> 
     */
    public static function collectFiles(string $dir, &$results = array(), $includeFolders = false): array
    {
        $files = scandir($dir);

        foreach ($files as $key => $value) {
            $path = realpath($dir . DIRECTORY_SEPARATOR . $value);
            if (!is_dir($path)) {
                $results[$path] = 'file';
            } else if ($value != "." && $value != "..") {
                if ($includeFolders) {
                    $results[$path] = 'folder';
                }
                self::collectFiles($path, $results, $includeFolders);
            }
        }
        return $results;
    }

    public static function debug($any, bool $checkEmpty = false): void
    {
        if ($checkEmpty && empty($any)) {
            return;
        }
        echo '<pre>';
        echo htmlentities(print_r($any, true));
        echo '</pre>';
    }
}
