<?php

use Vo\PageEngine;
use Vo\BaseComponent;

function RenderAppComponent_Slot3(
    Silly\MyApp\AppComponent $component,
    PageEngine $pageEngine,
    array $slots
    , ...$scope
) {
    $slotContents = [];
    
    $_content = '';

    $_content .= '
            Only title provided
            <br/>
        ';
    return $_content;
   
}
