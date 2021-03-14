<?php

namespace Viewi;

use Viewi\Routing\Router;

class App
{
    private static PageEngine $engine;

    public static array $config;

    public static function init(array $config)
    {
        $config[PageEngine::PUBLIC_BUILD_DIR] ??= '/viewi-build';
        self::$config = $config;
        self::$engine = new PageEngine(self::$config);
    }

    public static function run(string $component, array $params)
    {
        return self::$engine->render($component, $params);
    }

    public static function getEngine(): PageEngine
    {
        return self::$engine;
    }

    public static function handle(?string $url = null, string $method = null)
    {
        $url ??= isset($_SERVER['REDIRECT_URL']) ? $_SERVER['REDIRECT_URL'] : preg_replace('/\?.*/', '', $_SERVER['REQUEST_URI']);
        $method ??= $_SERVER['REQUEST_METHOD'];
        $response = Router::handle($url, $method, $_REQUEST);
        if (is_string($response)) { // html
            header("Content-type: text/html; charset=utf-8");
            echo $response;
        } else { // json
            header("Content-type: application/json; charset=utf-8");
            echo json_encode($response);
        }
    }
}
