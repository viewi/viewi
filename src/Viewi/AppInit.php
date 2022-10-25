<?php

namespace Viewi;

class AppInit
{
    protected array $config = [
        '__public_config' => [],
    ];

    public static function create(): AppInit
    {
        return new AppInit();
    }

    public function setSourceDir(string $path): AppInit
    {
        $this->config[PageEngine::SOURCE_DIR] = $path;
        return $this;
    }

    public function setServerBuildDir(string $path): AppInit
    {
        $this->config[PageEngine::SERVER_BUILD_DIR] = $path;
        return $this;
    }

    public function setPublicBuildDir(string $path): AppInit
    {
        $this->config[PageEngine::PUBLIC_BUILD_DIR] = $path;
        return $this;
    }

    public function setPublicRootDir(string $path): AppInit
    {
        $this->config[PageEngine::PUBLIC_ROOT_DIR] = $path;
        return $this;
    }

    public function setMode(bool $isDev = true): AppInit
    {
        $this->config[PageEngine::DEV_MODE] = $isDev;
        return $this;
    }

    public function setOutputMode(bool $returnHtml = true): AppInit
    {
        $this->config[PageEngine::RETURN_OUTPUT] = $returnHtml;
        return $this;
    }

    public function setPublicConfig(array $config): AppInit
    {
        $this->config['__public_config'] = $config;
        return $this;
    }

    public function getConfig(): array
    {
        return $this->config;
    }
}