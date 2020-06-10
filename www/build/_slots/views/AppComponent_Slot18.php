<?php

use Vo\PageEngine;
use Vo\BaseComponent;

function RenderAppComponent_Slot18(
    Silly\MyApp\AppComponent $component,
    PageEngine $pageEngine,
    array $slots
    , $uid, $user
) {
    $slotContents = [];
    
    $_content = '';

    $_content .= '';
    $_content .= htmlentities($uid);
    $_content .= ' ';
    $_content .= htmlentities($user->Name);
    $_content .= ' ';
    $_content .= htmlentities($user->Age);
    return $_content;
   
}
