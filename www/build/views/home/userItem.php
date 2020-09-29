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

    $_content .= 'USER: ';
    $slotContents[0] = 'UserItem_Slot8';
    $_content .= $pageEngine->renderComponent($slots[0] ? $slots[0] : 'UserItem_Slot8', $_component, $slotContents, [], ...$scope);
    $slotContents = [];
    $_content .= '
';
    $_content .= htmlentities($_component->title);
    $_content .= '
';
    if($_component->user !== null){
    
    $_content .= '
    ';
    $_content .= htmlentities($_component->user->Name);
    $_content .= '
';
    } else {
    
    $_content .= '
    User is not set
';
    }
    
    return $_content;
   
}
