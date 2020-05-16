<?php

function RenderAppComponentSlot4(
    AppComponent $component,
    PageEngine $pageEngine,
    array $slots
    , $uid, $user
) {
    $slotContents = [];
    ?><?=htmlentities($uid)?> <?=htmlentities($user->Name)?> <?=htmlentities($user->Age)?><?php   
}
