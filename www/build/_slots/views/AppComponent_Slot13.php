<?php

use Vo\PageEngine;
use Vo\BaseComponent;

function RenderAppComponent_Slot13(
    Silly\MyApp\AppComponent $component,
    PageEngine $pageEngine,
    array $slots
    , ...$scope
) {
    $slotContents = [];
    
    $_content = '';

    $_content .= '
    
    <div>SLOT by default 1</div>
    ';
    foreach($component->users as $uid => $user){
    
    $slotContents[0] = 'AppComponent_Slot14';
    $_content .= $pageEngine->renderComponent('UserItem', $component, $slotContents, [], $uid, $user);
    $slotContents = [];
    }
    
    $_content .= '
    ';
    foreach($component->users as $uid => $user){
    
    $_content .= '<div>
        Div Slot: ';
    $slotContents[0] = 'AppComponent_Slot15';
    $_content .= $pageEngine->renderComponent('UserItem', $component, $slotContents, [], $uid, $user);
    $slotContents = [];
    $_content .= '
    </div>';
    }
    
    $_content .= '
    
';
    return $_content;
   
}
