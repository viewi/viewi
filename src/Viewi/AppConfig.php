<?php

namespace Viewi;

class AppConfig
{
    /**
     * 
     * @param null|string $buildPath Destination folder for Viewi's build files
     * @param bool $devMode Development mode - each new request will trigger build process
     * @param null|string $sourcePath Path to your Viewi project
     * @param null|string $jsPath Path to the JavaScript project
     * @param null|string $publicPath Destination path for public assets
     * @param null|string $publicUrl Relative URL path for public assets
     * @param string[] $includes Additional components and packages
     * @return void 
     */
    public function __construct(
        public ?string $buildPath = null,
        public bool $devMode = false,
        public ?string $sourcePath = null,
        public ?string $jsPath = null,
        public ?string $publicPath = null,
        public ?string $publicUrl = null,
        public array $includes = []
    ) {
    }

    /**
     * Enables development mode - each new request will trigger build process
     * @return AppConfig 
     */
    public function developmentMode(?bool $mode = null): self
    {
        $this->devMode = $mode ?? true;
        return $this;
    }

    /**
     * 
     * @param string $sourcePath Path to your Viewi project
     * @return AppConfig 
     */
    public function buildFrom(string $sourcePath): self
    {
        $this->sourcePath = $sourcePath;
        return $this;
    }

    /**
     * 
     * @param string $buildPath Destination folder for Viewi's build files
     * @return AppConfig 
     */
    public function buildTo(string $buildPath): self
    {
        $this->buildPath = $buildPath;
        return $this;
    }

    /**
     * 
     * @param string $jsPath Path to the JavaScript project
     * @return AppConfig 
     */
    public function withJsEntry(string $jsPath): self
    {
        $this->jsPath = $jsPath;
        return $this;
    }

    /**
     * 
     * @param string $publicPath Destination path for public assets
     * @return AppConfig 
     */
    public function putAssetsTo(string $publicPath): self
    {
        $this->publicPath = $publicPath;
        return $this;
    }

    /**
     * 
     * @param string $publicUrl Relative URL path for public assets
     * @return AppConfig 
     */
    public function fetchAssetsFrom(string $publicUrl): self
    {
        $this->publicUrl = $publicUrl;
        return $this;
    }
}
