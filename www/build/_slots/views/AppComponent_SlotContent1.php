<?php

use Vo\PageEngine;
use Vo\BaseComponent;

function RenderAppComponent_SlotContent1(
    Silly\MyApp\AppComponent $component,
    PageEngine $pageEngine,
    array $slots
    , ...$scope
) {
    $slotContents = [];
    
    $_content = '';

    $_content .= '
        <title>App title</title>
    ';
    return $_content;
   
}
