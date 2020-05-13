<?php

function RenderAppComponent(AppComponent $component, PageEngine $pageEngine)
{
    ?><?=htmlentities((4+5)*3 + 4/((5+4)-1))?>

<?php $pageEngine->renderComponent($component->dynamicTag, $component, array (
  0 => 'AppComponentSlot1',
)); ?>

<a src="<?=htmlentities($component->url)?>">Home page</a>
<?php $pageEngine->renderComponent('HomePage', $component, array (
)); ?>

<?php $pageEngine->renderComponent('HomePage', $component, array (
  0 => 'AppComponentSlot2',
)); ?>

<br/>
"testing =*&%#@!)(*&" / 30$ > 15$ & 10 \
\
< 20 {O_o} hello ' ""' \noescape <hr/>
Content text "test string" 'another one' done.
<div data-name="test name is &quot;Mike&quot;." title="My dad's story"><?=htmlentities($component->testsList[1])?></div>

<p <?=htmlentities($component->dynamicAttr)?>="<?=htmlentities($component->dynValue)?>"><?=htmlentities($component->model)?>Model</p>
<?=htmlentities($component->about)?>

<div title="Title of <?=htmlentities($component->model)?> model">title test</div>
<p class="long-text test" title="Awesome">paragraph text</p>
<a><b>text</b></a>
<br attr="some value" data-test="hedge"/>
<div>
    <?=htmlentities($component->about)?> <?=htmlentities($component->testsList[0])?>

    <div>My friend <?=htmlentities($component->friend->Name)?> is <?=htmlentities($component->friend->Age)?> years old</div>
    <p><?=htmlentities($component->model)?>Model</p>
    <p>Name: <?=htmlentities($component->getFullName())?></p>

    <p>Occupation: <?=htmlentities($component->getOccupation())?></p>

</div>
<br/>
<b>1 + 1 is <?=htmlentities(1+1)?></b>

<header aria-disabled id="my-header"></header>
<f:table xmlns:f="https://www.w3schools.com/furniture">
    <f:name>Xml parsing demo</f:name>
    <f:width>80</f:width>
    <f:length>120</f:length>
</f:table>
plain text
<b>test</b>
<h1>
    header
</h1>
<?php $pageEngine->renderComponent('HomePage', $component, array (
)); ?>

<hr/>
<?php $pageEngine->renderComponent('HomePage', $component, array (
)); ?>

<?php $pageEngine->renderComponent('HomePage', $component, array (
  0 => 'AppComponentSlot4',
)); ?>

<footer>Footer</footer><?php
}
