<?php

namespace Viewi\Components\Assets;

use Viewi\BaseComponent;

class CssBundle extends BaseComponent
{
    public array $links = [];
    public string $link = '';
    public bool $minify = false;
    public bool $combine = false;
    public bool $inline = false;
    public bool $shakeTree = false;

    public function __version(): string
    {
        $key = implode('|', $this->links);
        $key .= $this->link;
        $key .= $this->minify ? '1' : '0';
        $key .= $this->inline ? '1' : '0';
        $key .= $this->shakeTree ? '1' : '0';
        $key .= $this->combine ? '1' : '0';
        return $key;
    }
}
