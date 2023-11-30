<?php

namespace Components\Views\Layouts;

use Viewi\Components\BaseComponent;
use Viewi\Components\Config\ConfigService;

class Layout extends BaseComponent
{
    public string $title = 'Viewi';
    public string $assetsUrl = '/';

    public function __construct(ConfigService $config)
    {
        $this->assetsUrl = $config->get('assetsUrl');
    }
}
