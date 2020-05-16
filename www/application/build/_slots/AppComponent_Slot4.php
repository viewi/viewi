<?php

function RenderAppComponent_Slot4(
    AppComponent $component,
    PageEngine $pageEngine,
    array $slots
    , $uid, $user
) {
    $slotContents = [];
    ?><?=htmlentities($uid)?> <?=htmlentities($user->Name)?> <?=htmlentities($user->Age)?><?php   
}
