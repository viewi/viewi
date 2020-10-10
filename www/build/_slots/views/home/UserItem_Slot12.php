<?php

use Viewi\PageEngine;
use Viewi\BaseComponent;

function RenderUserItem_Slot12(
    \UserItem $_component,
    PageEngine $pageEngine,
    array $slots
    , ...$scope
) {
    $slotContents = [];
    
    $_content = '';

    $_content .= '===no user===';
    return $_content;
   
}
