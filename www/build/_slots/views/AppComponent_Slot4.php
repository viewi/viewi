<?php

use Vo\PageEngine;
use Vo\BaseComponent;

function RenderAppComponent_Slot4(
    Silly\MyApp\AppComponent $component,
    PageEngine $pageEngine,
    array $slots
    , ...$scope
) {
    $slotContents = [];
    
    $_content = '';

    $_content .= '
   none
';
    return $_content;
   
}
