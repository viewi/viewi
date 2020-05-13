<?php

function RenderAppComponentSlot3(AppComponent $component, PageEngine $pageEngine, array $slots)
{
    ?>
        <?php $pageEngine->renderComponent('HomePage', $component, array (
  0 => 'AppComponentSlot4',
)); ?>

    <?php
}
