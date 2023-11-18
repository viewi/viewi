<?php

namespace Viewi;

class AppConfig
{
    /**
     * @param string Unique name
     * @param null|string $buildPath Destination folder for Viewi's build files
     * @param bool $devMode Development mode - each new request will trigger build process
     * @param null|string $sourcePath Path to your Viewi project
     * @param null|string $jsPath Path to the JavaScript project
     * @param null|string $publicPath Destination path for public assets
     * @param null|string $publicUrl Relative URL path for public assets
     * @param bool $minifyJs Enables minification for javascript build files
     * @param bool $combineJsJson Combines javascript bundle with JSON templates
     * @param bool $appendVersionPath Appends version/build id to every assets http request to avoid caching in the browser
     * @param bool $prod Enables production mode - no build process for each request
     * @param string[] $includes Additional components and packages
     * @return void 
     */
    public function __construct(
        public string $name = 'default',
        public ?string $buildPath = null,
        public bool $devMode = false,
        public ?string $sourcePath = null,
        public ?string $jsPath = null,
        public ?string $publicPath = null,
        public ?string $publicUrl = null,
        public bool $minifyJs = false,
        public bool $combineJsJson = false,
        public bool $appendVersionPath = false,
        public bool $prod = false,
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
     * Enables production mode - no build process for each request. 
     * Enables minification 
     * and appends version/build id to every asset's path.
     * @return AppConfig 
     */
    public function production(?bool $mode = null): self
    {
        $this->prod = $mode ?? true;
        if ($this->prod) {
            $this->devMode = false;
            $this->minifyJs = true;
            $this->appendVersionPath = true;
        }
        return $this;
    }

    /**
     * Enables minification for javascript build files
     * @param null|bool $minify 
     * @return AppConfig 
     */
    public function minfy(?bool $minify = null): self
    {
        $this->minifyJs = $minify ?? true;
        return $this;
    }

    /**
     * Combines javascript bundle with JSON templates. No separate http call for templates.
     * @param null|bool $combine 
     * @return AppConfig 
     */
    public function combine(?bool $combine = null): self
    {
        $this->combineJsJson = $combine ?? true;
        return $this;
    }

    /**
     * Appends version/build id to every assets http request to avoid caching in the browser.
     * Disable it for debuging javascript in the browser with disabled cache in dev tools.
     * @param null|bool $append 
     * @return AppConfig 
     */
    public function appendVersionToPath(?bool $append = null): self
    {
        $this->appendVersionPath = $append ?? true;
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
