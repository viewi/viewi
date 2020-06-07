<?php

use Vo\PageEngine;
use Vo\BaseComponent;

function RenderAppComponent_Slot4(
    Silly\MyApp\AppComponent $component,
    PageEngine $pageEngine,
    array $slots
    , $uid, $user
) {
    $slotContents = [];
    
    $_content = '';

    $_content .= '
            Arguments test
            <br/>
        ';
    return $_content;
   
}
