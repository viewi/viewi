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

    $slotContents['head'] = 'AppComponent_SlotContent1';

    $slotContents['body'] = 'AppComponent_SlotContent2';

    $slotContents[0] = 'AppComponent_Slot13';
    $_content .= $pageEngine->renderComponent('Layout', $component, $slotContents, [], ...$scope);
    $slotContents = [];
    return $_content;
   
}
