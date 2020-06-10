<?php

use Vo\PageEngine;
use Vo\BaseComponent;

function RenderUserItem_Slot19(
    \UserItem $component,
    PageEngine $pageEngine,
    array $slots
    , ...$scope
) {
    $slotContents = [];
    
    $_content = '';

    $_content .= 'no user';
    return $_content;
   
}
