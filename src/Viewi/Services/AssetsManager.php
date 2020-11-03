<?php

namespace Viewi\Services;

use Viewi\App;
use Viewi\PageEngine;

class AssetsManager
{
    public static function getViewiScriptsHtml(): string
    {
        $path = App::$config[PageEngine::PUBLIC_URL_PATH];
        $combine = App::$config[PageEngine::COMBINE_JS] ?? false;
        $minify = App::$config[PageEngine::MINIFY] ?? false;
        $async = $combine ? 'async' : '';
        $scripts = $minify ?
            "<script $async src=\"$path/app.min.js\"></script>"
            : "<script $async src=\"$path/app.js\"></script>";
        if (!$combine) {
            $scripts =
                ($minify ?
                    "<script src=\"$path/bundle.min.js\"></script>"
                    : "<script src=\"$path/bundle.js\"></script>") .
                $scripts;
        }
        return $scripts;
    }
}
