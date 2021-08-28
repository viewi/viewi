<?php

namespace Viewi\Common;

use Viewi\App;

class ConfigService
{
    public function getConfig(): array
    {
        return App::$publicConfig;
    }
}
