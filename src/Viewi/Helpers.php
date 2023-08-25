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

    /**
     * 
     * @param mixed $path 
     * @param bool $removeRoot 
     * @return void 
     */
    public static function removeDirectory($path, $removeRoot = false)
    {
        $files = glob($path . '/*');
        foreach ($files as $file) {
            is_dir($file) ? self::removeDirectory($file, true) : unlink($file);
        }
        if ($removeRoot) {
            rmdir($path);
        }
        return;
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
