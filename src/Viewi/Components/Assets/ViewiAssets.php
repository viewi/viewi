<?php

namespace Viewi\Components\Assets;

use Viewi\Components\Attributes\Preserve;
use Viewi\Components\BaseComponent;
use Viewi\DI\Singleton;

#[Singleton]
class ViewiAssets extends BaseComponent
{
    #[Preserve]
    public string $appPath = '';

    #[Preserve]
    public string $data = '<script data-keep="ViewiAssets">"ViewiAssets";</script>';
}
