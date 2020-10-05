<?php

use Vo\PageEngine;
use Vo\BaseComponent;

function RenderUserItem_Slot11(
    \UserItem $_component,
    PageEngine $pageEngine,
    array $slots
    , ...$scope
) {
    $slotContents = [];
    
    $_content = '';

    $_content .= 'no user';
    return $_content;
   
}
