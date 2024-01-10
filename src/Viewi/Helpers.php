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
    public static function removeDirectory(string $path, bool $removeRoot = false): void
    {
        $files = glob($path . '/*');
        foreach ($files as $file) {
            if (file_exists($file)) {
                is_dir($file) ? self::removeDirectory($file, true) : unlink($file);
            }
        }

        if ($removeRoot) {
            rmdir($path);
        }
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

    public static function copyAll(string $fromPath, string $toPath): void
    {
        $resources = [];
        $relFromPath = realpath($fromPath);
        if ($relFromPath) {
            $fromPath = $relFromPath;
        }
        self::collectFiles($fromPath, $resources, true);
        foreach ($resources as $path => $type) {
            $basePath = str_replace($fromPath, '', $path);
            $destinationPath = $toPath . $basePath;
            // $this->debug([$type, $fromPath, $path, $basePath, $toPath, $destinationPath]);
            switch ($type) {
                case 'folder':
                {
                    if (!file_exists($destinationPath)) {
                        mkdir($destinationPath, 0777, true);
                    }
                    break;
                }
                case 'file':
                default:
                {
                    // file
                    file_put_contents($destinationPath, file_get_contents($path));
                }
            }
        }
    }

    public static function randomHex(int $length = 8)
    {
        $data = random_bytes($length);
        return bin2hex($data);
    }

    public static function randomInt(int $length = 8)
    {
        $data = random_int(10 ** ($length - 1), (10 ** $length) - 1);
        return $data;
    }

    public static function randomString(int $length = 8)
    {
        $bytes = random_bytes($length);
        $randomString = substr(str_replace(['/', '+', '='], '', base64_encode($bytes)), 0, $length);
        return $randomString;
    }
}
