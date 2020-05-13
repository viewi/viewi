<?php

function RenderAppComponent(AppComponent $component, PageEngine $pageEngine, array $slots)
{
    ?><?php $pageEngine->renderComponent('HomePage', $component, array (
)); ?>

<?php $pageEngine->renderComponent('HomePage', $component, array (
  0 => 'AppComponentSlot1',
)); ?><?php
}
