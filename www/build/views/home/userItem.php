<?php

use Vo\PageEngine;
use Vo\BaseComponent;

function RenderUserItem(
    \UserItem $_component,
    PageEngine $pageEngine,
    array $slots
    , ...$scope
) {
    $slotContents = [];
    
    $_content = '';

    $_content .= 'USER: 
<b>Order: ';
    $_content .= htmlentities($_component->order);
    $_content .= '</b>';
    return $_content;
   
}
