<?php

use Vo\PageEngine;
use Vo\BaseComponent;

function RenderAppComponent_Slot10(
    Silly\MyApp\AppComponent $component,
    PageEngine $pageEngine,
    array $slots
    , ...$scope
) {
    $slotContents = [];
    
    $_content = '';

    $_content .= '
                    ';
    $slotContents[0] = 'AppComponent_Slot11';
    $_content .= $pageEngine->renderComponent($component->dynamicTag, $component, $slotContents, [], ...$scope);
    $slotContents = [];
    $_content .= '
                ';
    return $_content;
   
}
