<?php

use Vo\PageEngine;
use Vo\BaseComponent;

function RenderAppComponent_Slot7(
    Silly\MyApp\AppComponent $component,
    PageEngine $pageEngine,
    array $slots
    , ...$scope
) {
    $slotContents = [];
    
    $_content = '';

    $_content .= '';
    $_content .= htmlentities($component->content);
    return $_content;
   
}
