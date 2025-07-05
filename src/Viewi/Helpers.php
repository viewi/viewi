<?php

namespace Viewi;

use Exception;
use Viewi\TemplateParser\TagItem;
use Viewi\TemplateParser\TagItemType;

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
        $files = scandir($path);

        foreach ($files as $key => $value) {
            $file = realpath($path . DIRECTORY_SEPARATOR . $value);
            if ($value === "." || $value === "..") {
                continue;
            }
            if (file_exists($file)) {
                is_dir($file) ? self::removeDirectory($file, true) : unlink($file);
            }
        }
        if ($removeRoot) {
            rmdir($path);
            // if (!rmdir($path)) {
            //     echo new Exception()->getTraceAsString();
            //     die();
            // }
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

    public static function copyAll(string $fromPath, string $toPath, bool $override = true): void
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
                case 'folder': {
                        if (!file_exists($destinationPath)) {
                            mkdir($destinationPath, 0777, true);
                        }
                        // print_r([
                        //     $path . DIRECTORY_SEPARATOR,
                        //     $toPath . basename($basePath) . DIRECTORY_SEPARATOR
                        // ]);
                        // self::copyAll($path . DIRECTORY_SEPARATOR,  $toPath . basename($basePath) . DIRECTORY_SEPARATOR,  $override);
                        break;
                    }
                case 'file':
                default: {
                        // file
                        $content = file_get_contents($path);
                        $write = $override || !file_exists($destinationPath) || $content !== file_get_contents($destinationPath);
                        // print_r([$path, $destinationPath]);
                        if ($write) {
                            file_put_contents($destinationPath, $content);
                        }
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


    public static function prettyOutput(TagItem $tagItem, $indentation = '')
    {
        $output = "";

        $output .= PHP_EOL . $indentation . ($tagItem->Type->Name !== TagItemType::Root ? $tagItem->Content : 'ROOT');
        foreach ($tagItem->getChildren() as &$child) {
            if ($child->Type->Name === TagItemType::Tag) {
                $output .= ":" . self::prettyOutput($child, $indentation . '  ');
            }
        }
        return $output;
    }

    public static function errorOutput(?string $fileOrName, string $html, int $position, int $highlightSize = 3, int $highlightAfter = 0)
    {
        if (!$fileOrName) {
            $fileOrName = 'INLINE';
        }
        $length = 100;
        if ($position < $length) {
            $length = $position;
        }
        if ($highlightSize <= 0 && $highlightAfter <= 0) {
            $highlightSize = 3;
        }
        $start = max(0, $position - $length);
        $line = count(explode(PHP_EOL, substr($html, 0, $position)));
        $before = substr($html, $start, $length - $highlightSize);
        $mid = substr($html, $start + $length - $highlightSize, $highlightSize + $highlightAfter);
        $after = substr($html, $start + $length + $highlightAfter, 10);

        $output = self::terminalBold(self::terminalGreen($fileOrName))
            . PHP_EOL
            . "LINE: $line"
            . PHP_EOL
            . $before
            . self::terminalCurlyUnderline(self::terminalBold(self::terminalRed($mid)))
            . $after;

        return $output;
    }

    // https://askubuntu.com/questions/528928/how-to-do-underline-bold-italic-strikethrough-color-background-and-size-i
    public static function terminalGreen(string $text)
    {
        return "\e[38;5;42m$text\e[39m";
    }

    public static function terminalRed(string $text)
    {
        return "\e[31m{$text}\e[0m";
    }

    public static function terminalOrange(string $text)
    {
        return "\033[35m{$text}\033[0m";
    }

    public static function terminalBold(string $text)
    {
        return "\e[1m{$text}\e[0m";
    }

    public static function terminalCurlyUnderline(string $text)
    {
        return "\e[4:3m{$text}\e[4:0m";
    }
}
