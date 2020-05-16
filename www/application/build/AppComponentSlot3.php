<?php

function RenderAppComponentSlot3(
    AppComponent $component,
    PageEngine $pageEngine,
    array $slots
    , $uid, $user
) {
    $slotContents = [];
    ?>
            user-<?=htmlentities($uid)?>

            <?php
    if($user->Age < 31){
    ?><div>
                <b>
                    Age < 31: <?=htmlentities($uid)?> <?=htmlentities($user->Name)?> <?=htmlentities($user->Age)?>

                </b>
            </div><?php
    } else if ($user->Name === 'Jhonz Doeb'){
    ?><div>
                <b>
                    else-if Jhonz Doeb: <?=htmlentities($uid)?> <?=htmlentities($user->Name)?> <?=htmlentities($user->Age)?>

                </b>
            </div><?php
    } else if ($user->Age === 32){
    ?><div>
                <b>
                    else-if age 32: <?=htmlentities($uid)?> <?=htmlentities($user->Name)?> <?=htmlentities($user->Age)?>

                </b>
            </div><?php
    } else {
    ?><div>
                <b>
                    else: <?=htmlentities($uid)?> <?=htmlentities($user->Name)?> <?=htmlentities($user->Age)?>

                </b>
            </div><?php
    }
    ?>

        <?php   
}
