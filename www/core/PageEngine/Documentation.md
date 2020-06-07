## Page Engine

### Powerfull view engine for your application

**Code:**
component: *app/home.php*
```php
<?php

use Vo\BaseComponent;

class HomeComponent extends BaseComponent
{
    public string $title = 'My awesome application';

    function getFullName(): string
    {
        return 'Jhon Doe';
    }

    function getOccupation(): string
    {
        return 'Web developer';
    } 
}

```
template: *app/home.html*
```html
<h1>$title</h1>
<p>Name: {getFullName()}</p>
<p>Occupation: {getOccupation()}</p>
```
**Result:**
```html
<h1>My awesome application</h1>
<p>Name: Jhon Doe</p>
<p>Occupation: Web developer</p>
```

### How to use
```php
// true if you in developing mode
$develop = true;

// true if you want render into variable, otherwise - echo output
$renderReturn = true;

$page = new Vo\PageEngine(
    'path/to/your/components',
    'build/path',
    $develop,
    $renderReturn
);

// render selected component, for example HomeComponent
$html = $page->render(HomeComponent::class);

```

### Supported features

**Render variable**
`<div>$myVar</div>` or `<div>{$myVar}</div>`. All values are automatically escaped.

**Render method\`s call result**
`<div>{method()}</div>`. All values are automatically escaped.

**Render raw html**
`<div>{{$raw}}</div>`

**Render component**
Put component class name (without namespace) as a tag in your template.
For example:
component: *app/HomeLink.php*
```php
<?php

use Vo\BaseComponent;

class HomeLink extends BaseComponent
{
    public string $href = '/';
}

```
template: *app/HomeLink.html*
```html
<a href="$href">Home</a>
```

component: *app/home.php*
```php
<?php
// see HomeComponent at the top
```
template: *app/home.html*
```html
<h1>$title</h1>
<HomeLink></HomeLink>
```
*Result:*
```html
<h1>My awesome application</h1>
<a href="/">Home</a>
```

**Slots**
You can pass content into component which will be rendered instead of `<slot>` tag. Also you can specify default content (optional).
template: *app/HomeLink.html*
```html
<a href="$href"><slot>Home</slot></a>
```
template: *app/home.html*
```html
<h1>$title</h1>
<HomeLink>Custom text</HomeLink>
<HomeLink></HomeLink>
```
*Result:*
```html
<h1>My awesome application</h1>
<a href="/">Custom text</a>
<a href="/">Home</a>
```

**Named slots**
You can also have named slots using `<slot name="top">` tag with name attribute. To specify content for named slot you should use `<slotContent name="top">` tag with name attribute. Slot without name attribute bacames slot by default and any content outside `<slotContent..` tag becames content for default slot `<slot>` (without name attribute).
template: *app/HomeLink.html*
```html
<slot name="top"></slot>
<a href="$href"><slot>Home</slot></a>
<slot name="bottom"></slot>
```
template: *app/home.html*
```html
<h1>$title</h1>
<HomeLink>
    <!-- this will go to <slot name="top"> -->
    <slotContent name="top">
        <p>Top content</p>
    </slotContent>
    <!-- this will go to <slot> -->
    slot by default
    <!-- We don't specify <slot name="bottom">, so it will be empty -->
</HomeLink>
```
*Result:*
```html
<h1>My awesome application</h1>
        <p>Top content</p>
    <a href="/">slot by default</a>
```

**If statement**
`<tag if="$condition"...`
```php
//...
class HomeComponent extends BaseComponent
{
    public bool $active = true;
//...
```
template: *app/home.html*
```html
<h1>$title</h1>
<div if="$active">Will be rendered if active is true</div>
```
*Result:*
```html
<h1>My awesome application</h1>
<div>Will be rendered if active is true</div>
```

**Foreach**
```php
<tag foreach="$array as $item"..
//or
<tag foreach="$array as $key => $item"..
```
```php
//...
class HomeComponent extends BaseComponent
{
    public array $fruits = ['Orange', 'Apple', 'Banana'];
//...
```
template: *app/home.html*
```html
<h1>$title</h1>
<div foreach="$fruits as $fruit">$fruit</div>
```
*Result:*
```html
<h1>My awesome application</h1>
<div>Orange</div>
<div>Apple</div>
<div>Banana</div>
```

**If and foreach combinations**
You can have `if` and `foreach` together, but order matters: 
This will check `if` condition first, and if i'ts true will execute `foreach`
```php
<div if="$condition" foreach="$array as $item"...`
```
And this will run `foreach` first and then check `if` condition for each item
```php
<div foreach="$array as $item" if="$item->active"...`
```

**DI**

****

**Template**
You can use tag `<template>` to group elements into one logical entity on one side, and on the other side only `<template>` content will be rendered. Usefull when use in combination with `if` or/and `foreach`.
template: *app/Links.html*
```html
<template>
    <a href="$href">Back home</a>
    <a href="/blog">Blog</a>
</template>
```
template: *app/home.html*
```html
<h1>$title</h1>
<Links></Links>
```
*Result:*
```html
<h1>My awesome application</h1>
    <a href="/">Back home</a>
    <a href="/blog">Blog</a>
```