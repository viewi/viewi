<?php

use Vo\PageEngine;
use Vo\BaseComponent;

function RenderAppComponent_Slot17(
    Silly\MyApp\AppComponent $component,
    PageEngine $pageEngine,
    array $slots
    , $uid, $user
) {
    $slotContents = [];
    
    $_content = '';

    $_content .= '
        UserItem Slot: ';
    $_content .= htmlentities($uid);
    $_content .= ' ';
    $_content .= htmlentities($user->Name);
    $_content .= ' ';
    $_content .= htmlentities($user->Age);
    $_content .= '
    ';
    return $_content;
   
}
