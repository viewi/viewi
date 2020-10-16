<?php

namespace Viewi;

abstract class BaseComponent
{
    function __invoke($params)
    {
        return App::run(get_class($this), $params);
    }
}
