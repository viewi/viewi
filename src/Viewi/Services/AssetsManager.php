<?php

namespace Viewi\Services;

use Viewi\App;
use Viewi\Components\Assets\CssBundle;
use Viewi\PageEngine;
use Viewi\PageTemplate;

class AssetsManager
{
    public static int $queueNumber = 0;
    public static function getViewiScriptsHtml(): string
    {
        $path = App::$config[PageEngine::PUBLIC_URL_PATH] ?? App::$config[PageEngine::PUBLIC_BUILD_DIR];
        $combine = App::$config[PageEngine::COMBINE_JS] ?? false;
        $minify = App::$config[PageEngine::MINIFY] ?? false;
        $dev = App::$config[PageEngine::DEV_MODE];
        $version = $dev ? '' : '?v=' . date('ymdHis');
        $async = $combine ? 'defer' : 'defer';
        $scripts = $minify ?
            "<script $async src=\"$path/app.min.js$version\"></script>"
            : "<script $async src=\"$path/app.js$version\"></script>";
        if (!$combine) {
            $scripts =
                ($minify ?
                    "<script $async src=\"$path/bundle.min.js$version\"></script>"
                    : "<script $async src=\"$path/bundle.js$version\"></script>") .
                $scripts;
        }
        return $scripts;
    }

    public static function scheduleStylesProcessing(CssBundle $bundle, PageEngine $_pageEngine): string
    {
        $queueNumber = self::$queueNumber++;
        $contentKey = "### $queueNumber ###";
        $_pageEngine->putIntoPostProcessQueue(function () use ($bundle, $_pageEngine, $contentKey) {
            $content = self::getViewiStylesHtml($bundle, $_pageEngine);
            $templates = $_pageEngine->getTemplates();
            foreach ($templates as $template) {
                if ($template->ComponentInfo->Name === 'CssBundle') {
                    foreach ($template->ComponentInfo->Versions as $version => $templateInfo) {
                        /**
                         * @var PageTemplate
                         */
                        $template = $templateInfo['Template'];
                        $template->PhpHtmlContent = str_replace($contentKey, $content, $template->PhpHtmlContent);
                        $template->RootTag->getChildren()[0]->Content = str_replace($contentKey, $content, $template->RootTag->getChildren()[0]->Content);
                    }
                }
            }
        });
        return $contentKey;
    }

    public static function getViewiStylesHtml(CssBundle $bundle, PageEngine $_pageEngine): string
    {
        $html = '';
        $rootDir = App::$config[PageEngine::PUBLIC_ROOT_DIR];
        $buildDir = App::$config['PUBLIC_BUILD_DIR'];
        $combined = '';
        $dev = App::$config[PageEngine::DEV_MODE];
        $version = $dev ? '' : '?v=' . date('ymdHis');
        $componentVersion = $bundle->__version();
        $minifyService = new MinifyService();
        $treeShakeService = new TreeShakingService($_pageEngine);
        if ($bundle->link) {
            $cssName = $bundle->link;
            $cssContent = ($bundle->combine || $bundle->minify || $bundle->shakeTree) ? file_get_contents($rootDir . $cssName) : '';
            if ($bundle->shakeTree) {
                $cssContent = $treeShakeService->shakeCss($cssContent);
                $cssName = $buildDir . '/' . basename($cssName, '.css') . '.shk.css';
                $newFileName =  $rootDir . $cssName;
                file_put_contents($newFileName, $cssContent);
            }
            if ($bundle->minify) {
                $cssContent = $minifyService->minifyCss($cssContent);
                $cssName = $buildDir . '/' . basename($cssName, '.css') . '.min.css';
                $newFileName =  $rootDir . $cssName;
                file_put_contents($newFileName, $cssContent);
            }
            if ($bundle->combine) {
                $combined .= $cssContent;
            } else {
                $html .= $bundle->inline ?
                    '<style>' . file_get_contents($rootDir . $cssName) . '</style>' :
                    "<link rel=\"stylesheet\" href=\"{$cssName}$version\">";
            }
        }
        foreach ($bundle->links as $link) {
            $cssName = $link;
            $cssContent = ($bundle->combine || $bundle->minify || $bundle->shakeTree) ? file_get_contents($rootDir . $cssName) : '';
            if ($bundle->shakeTree) {
                $cssContent = $treeShakeService->shakeCss($cssContent);
                $cssName = $buildDir . '/' . basename($cssName, '.css') . '.shk.css';
                $newFileName =  $rootDir . $cssName;
                file_put_contents($newFileName, $cssContent);
            }
            if ($bundle->minify) {
                $cssContent = $minifyService->minifyCss($cssContent);
                $cssName = $buildDir . '/' . basename($cssName, '.css') . '.min.css';
                $newFileName =  $rootDir . $cssName;
                file_put_contents($newFileName, $cssContent);
            }
            if ($bundle->combine) {
                $combined .= $cssContent . ($bundle->minify ? '' : PHP_EOL);
            } else {
                $html .= $bundle->inline ?
                    '<style>' . file_get_contents($rootDir . $cssName) . '</style>' :
                    "<link rel=\"stylesheet\" href=\"{$cssName}$version\">";
            }
        }
        if ($bundle->combine) {
            $newName = $buildDir . '/' . crc32($componentVersion) . '.css';
            $newFileName =  $rootDir . $newName;
            file_put_contents($newFileName, $combined);
            $html .= $bundle->inline ?
                '<style>' . $combined . '</style>' :
                "<link rel=\"stylesheet\" href=\"{$newName}$version\">";
        }
        return $html;
    }
}
