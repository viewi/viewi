<?php

use Vo\PageEngine;
use Vo\BaseComponent;

function RenderAppComponent(
    Silly\MyApp\AppComponent $component,
    PageEngine $pageEngine,
    array $slots
    , ...$scope
) {
    $slotContents = [];
    
    $_content = '';

    $slotContents[0] = 'AppComponent_Slot4';
    $_content .= $pageEngine->renderComponent('Layout', $component, $slotContents, [], ...$scope);
    $slotContents = [];
    return $_content;
   
}
