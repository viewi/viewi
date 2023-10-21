<?php

namespace Viewi\Components\Assets;

use Viewi\Components\BaseComponent;
use Viewi\DI\Singleton;

#[Singleton]
class ViewiAssets extends BaseComponent
{
    public string $appPath = '';
    public string $data = '<script>console.log("ViewiAssets");</script>';
}
