<?php

namespace Viewi\Services;

use Viewi\App;
use Viewi\Components\Assets\CssBundle;
use Viewi\PageEngine;

class AssetsManager
{
    public static function getViewiScriptsHtml(): string
    {
        $path = App::$config[PageEngine::PUBLIC_URL_PATH] ?? App::$config[PageEngine::PUBLIC_BUILD_DIR];
        $combine = App::$config[PageEngine::COMBINE_JS] ?? false;
        $minify = App::$config[PageEngine::MINIFY] ?? false;
        $dev = App::$config[PageEngine::DEV_MODE];
        $version = $dev ? '' : '?v=' . date('ymdHis');
        $async = $combine ? 'async' : '';
        $scripts = $minify ?
            "<script $async src=\"$path/app.min.js$version\"></script>"
            : "<script $async src=\"$path/app.js$version\"></script>";
        if (!$combine) {
            $scripts =
                ($minify ?
                    "<script src=\"$path/bundle.min.js$version\"></script>"
                    : "<script src=\"$path/bundle.js$version\"></script>") .
                $scripts;
        }
        return $scripts;
    }

    public static function getViewiStylesHtml(CssBundle $bundle): string
    {
        $html = '';
        $rootDir = App::$config[PageEngine::PUBLIC_ROOT_DIR];
        if ($bundle->link) {
            $html .= $bundle->inline ?
                '<style>' . file_get_contents($rootDir . $bundle->link) . '</style>' :
                "<link rel=\"stylesheet\" href=\"{$bundle->link}\">";
        }
        foreach ($bundle->links as $link) {
            $html .= $bundle->inline ?
                '<style>' . file_get_contents($rootDir . $link) . '</style>' :
                "<link rel=\"stylesheet\" href=\"{$link}\">";
        }
        return $html;
    }
}
