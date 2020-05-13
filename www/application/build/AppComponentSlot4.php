<?php

function RenderAppComponentSlot4(AppComponent $component, PageEngine $pageEngine, array $slots)
{
    ?>
            <?php $pageEngine->renderComponent('HomePage', $component, array (
  0 => 'AppComponentSlot5',
)); ?>

        <?php
}
