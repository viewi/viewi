<?php

use Vo\PageEngine;
use Vo\BaseComponent;

function RenderAppComponent(
    Silly\MyApp\AppComponent $_component,
    PageEngine $pageEngine,
    array $slots
    , ...$scope
) {
    $slotContents = [];
    
    $_content = '';

    $slotContents['body'] = 'AppComponent_SlotContent6';

    $slotContents[0] = 'AppComponent_Slot7';
    $_content .= $pageEngine->renderComponent('Layout', $_component, $slotContents, [], ...$scope);
    $slotContents = [];
    return $_content;
   
}
