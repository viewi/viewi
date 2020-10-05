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
    $slotContents[0] = 'UserItem_Slot6';
    $_content .= $pageEngine->renderComponent($slots[0] ? $slots[0] : 'UserItem_Slot6', $_component, $slotContents, [], ...$scope);
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
    
    $_content .= '
';
    if($_component->active){
    
    $_content .= '<span>
    Active
</span>';
    } else {
    
    $_content .= '<span>
    NOT Active
</span>';
    }
    
    $_content .= '
<b>Order: ';
    $_content .= htmlentities($_component->order);
    $_content .= '</b>';
    return $_content;
   
}
