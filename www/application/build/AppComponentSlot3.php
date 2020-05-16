<?php

function RenderAppComponentSlot3(AppComponent $component, PageEngine $pageEngine, array $slots
    , $uid, $user
)
{
    $slotContents = [];
    ?>
            UserItem: <?=htmlentities($uid)?> <?=htmlentities($user->Name)?> <?=htmlentities($user->Age)?>

        <?php   
}
