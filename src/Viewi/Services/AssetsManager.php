<?php

namespace Viewi\Services;

use Viewi\App;
use Viewi\PageEngine;

class AssetsManager
{
    public static function getViewiScriptsHtml(): string
    {
        $path = App::$config[PageEngine::PUBLIC_URL_PATH] ?? '/';
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
}
