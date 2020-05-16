<?php

function RenderLayout(Layout $component, PageEngine $pageEngine, array $slots
    , ...$scope
)
{
    $slotContents = [];
    ?><!DOCTYPE html>
<html>

<head>
    <?php
    $slotContents[0] = 'LayoutSlot16';
    $pageEngine->renderComponent($slots['head'] ? $slots['head'] : 'LayoutSlot16', $component, $slotContents, ...$scope);
?>
</head>

<body>
    <?php $pageEngine->renderComponent($slots['body'], $component, []); ?>
    <?php $pageEngine->renderComponent($slots[0], $component, []); ?>
</body>

</html><?php   
}
