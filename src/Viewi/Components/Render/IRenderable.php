<?php

namespace Viewi\Components\Render;

/**
 * Server-side custom render implementation
 * @package Viewi\Components\Render
 */
interface IRenderable
{
    function render(RenderContext $renderMeta): string;
}
