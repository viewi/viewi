<?php

use Vo\PageEngine;
use Vo\BaseComponent;

function RenderAppComponent_Slot12(
    Silly\MyApp\AppComponent $component,
    PageEngine $pageEngine,
    array $slots
    , ...$scope
) {
    $slotContents = [];
    
    $_content = '';

    $_content .= '
            <span>render inside ';
    $_content .= htmlentities($component->model);
    $_content .= '</span>
        ';
    return $_content;
   
}
