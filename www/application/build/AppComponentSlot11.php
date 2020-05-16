<?php

function RenderAppComponentSlot11(AppComponent $component, PageEngine $pageEngine, array $slots
    , ...$scope
)
{
    $slotContents = [];
    ?>
    
    <div>SLOT by default 1</div>
    <?php
    foreach($component->users as $uid => $user){
    ?><?php
    $slotContents[0] = 'AppComponentSlot12';
    $pageEngine->renderComponent('UserItem', $component, $slotContents, $uid, $user);
?><?php
    }
    ?>

    <?php
    foreach($component->users as $uid => $user){
    ?><div>
        Div Slot: <?php
    $slotContents[0] = 'AppComponentSlot13';
    $pageEngine->renderComponent('UserItem', $component, $slotContents, $uid, $user);
?>

    </div><?php
    }
    ?>
    
<?php   
}
