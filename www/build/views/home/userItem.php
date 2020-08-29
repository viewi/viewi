<?php

use Vo\PageEngine;
use Vo\BaseComponent;

function RenderUserItem(
    \UserItem $component,
    PageEngine $pageEngine,
    array $slots
    , ...$scope
) {
    $slotContents = [];
    
    $_content = '';

    $_content .= 'USER: ';
    $slotContents[0] = 'UserItem_Slot6';
    $_content .= $pageEngine->renderComponent($slots[0] ? $slots[0] : 'UserItem_Slot6', $component, $slotContents, [], ...$scope);
    $slotContents = [];
    $_content .= '
';
    $_content .= htmlentities($component->title);
    $_content .= '
';
    if($component->user!==null){
    
    $_content .= '
    ';
    $_content .= htmlentities($component->user->Name);
    $_content .= '
';
    } else {
    
    $_content .= '
    User is not set
';
    }
    
    return $_content;
   
}
