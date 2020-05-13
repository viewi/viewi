<?php

function RenderHomePage(HomePage $component, PageEngine $pageEngine, array $slots)
{
    ?><div>
    <?=htmlentities($component->title)?>

    <div>
        <span>Slot testing</span>
        <?php $pageEngine->renderComponent($slots[0], $component, []); ?>
    </div>
</div><?php
}
