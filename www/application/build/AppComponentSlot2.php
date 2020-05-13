<?php

function RenderAppComponentSlot2(AppComponent $component, PageEngine $pageEngine)
{
    ?>
    <?php $pageEngine->renderComponent('HomePage', $component, array (
  0 => 'AppComponentSlot3',
)); ?>

<?php
}
