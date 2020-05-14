<?php

function RenderAppComponent(AppComponent $component, PageEngine $pageEngine, array $slots)
{
    $slotContents = [];
    ?><?php $slotContents['head'] = 'AppComponentSlotComponent1'; ?><?php $slotContents['body'] = 'AppComponentSlotComponent2'; ?><?php
$slotContents[] = 'AppComponentSlot9';
$pageEngine->renderComponent('Layout', $component, $slotContents);
?><?php   
}
