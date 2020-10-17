<?php

namespace Viewi;

class App
{
    private static PageEngine $engine;

    public static array $config;

    public static function init(array $config)
    {
        self::$config = $config;
        self::$engine = new PageEngine(
            $config[PageEngine::SOURCE_DIR],
            $config[PageEngine::SERVER_BUILD_DIR],
            $config[PageEngine::PUBLIC_BUILD_DIR],
            $config[PageEngine::DEV_MODE],
            $config[PageEngine::RETURN_OUTPUT]
        );
    }

    public static function run(string $component, array $params)
    {
        return self::$engine->render($component, $params);
    }
}
