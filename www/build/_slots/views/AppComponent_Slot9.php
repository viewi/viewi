<?php

use Vo\PageEngine;
use Vo\BaseComponent;

function RenderAppComponent_Slot9(
    Silly\MyApp\AppComponent $component,
    PageEngine $pageEngine,
    array $slots
    , ...$scope
) {
    $slotContents = [];
    
    $_content = '';

    $_content .= '
                ';
    $slotContents[0] = 'AppComponent_Slot10';
    $_content .= $pageEngine->renderComponent('HomePage', $component, $slotContents, [], ...$scope);
    $slotContents = [];
    $_content .= '
            ';
    return $_content;
   
}