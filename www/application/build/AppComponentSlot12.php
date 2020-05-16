<?php

function RenderAppComponentSlot12(
    AppComponent $component,
    PageEngine $pageEngine,
    array $slots
    , $uid, $user
) {
    $slotContents = [];
    ?>
        UserItem Slot: <?=htmlentities($uid)?> <?=htmlentities($user->Name)?> <?=htmlentities($user->Age)?>

    <?php   
}
