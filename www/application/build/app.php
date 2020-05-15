<?php

function RenderAppComponent(AppComponent $component, PageEngine $pageEngine, array $slots)
{
    $slotContents = [];
    ?><?php 
$slotContents['head'] = 'AppComponentSlotContent1';
?><?php 
$slotContents['body'] = 'AppComponentSlotContent2';
?><?php
$slotContents[0] = 'AppComponentSlot9';
$pageEngine->renderComponent('Layout', $component, $slotContents);
?><?php   
}
