<?php

function RenderAppComponentSlot5(AppComponent $component, PageEngine $pageEngine, array $slots)
{
    ?>
                <?php $pageEngine->renderComponent($component->dynamicTag, $component, array (
  0 => 'AppComponentSlot6',
)); ?>

            <?php
}
