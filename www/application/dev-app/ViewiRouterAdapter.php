<?php

use DevApp\DevRouter;
use Viewi\Routing\RouteAdapterBase;

class ViewiRouteAdapter extends RouteAdapterBase
{

    public function handle($method, $url)
    {
        return DevRouter::handle($url, $method);
    }

    public function register($method, $url, $component, $defaults)
    {
        DevRouter::register($method, $url, $component);
    }
}
