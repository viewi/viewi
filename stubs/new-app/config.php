<?php

use Viewi\AppConfig;

$d = DIRECTORY_SEPARATOR;
$viewiAppPath = __DIR__ . $d;
$componentsPath =  $viewiAppPath . 'Components';
$buildPath = $viewiAppPath . 'build';
$jsPath = $viewiAppPath . 'js';
$assetsSourcePath = $viewiAppPath . '##public##';
$publicPath = __DIR__ . $d . '..' . $d . '##public##';
$assetsPublicUrl = '/assets';

return (new AppConfig())
    ->buildTo($buildPath)
    ->buildFrom($componentsPath)
    ->withJsEntry($jsPath)
    ->putAssetsTo($publicPath)
    ->assetsPublicUrl($assetsPublicUrl)
    ->withAssets($assetsSourcePath)
    // ->combine()
    ->developmentMode(true)
    ->buildJsSourceCode()
    ->watchWithNPM(true);
