# Outdated

Please read the documentation here: [https://viewi.net/docs](https://viewi.net/docs)

# Viewi Page Engine

This is the shortened version of how to use the available features. A more detailed documentation is in progress.

##### *Requirements: php 7.4, 8+*

## Install

`composer require viewi/viewi:dev-master`

**Example Code:**
component: *app/home.php*
```php
<?php

use Viewi\BaseComponent;

class HomeComponent extends BaseComponent
{
    public string $title = 'My awesome application';
    
    function getFullName(): string
    {
        return 'John Doe';
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
<p>Name: John Doe</p>
<p>Occupation: Web developer</p>
```

**Note:** home.php and home.html must be on the same directory.

### How to use
```php
use Viewi\PageEngine;

// true if you are in developing mode
$develop = true;

// true if you want to render into variable, otherwise - echo output
$renderReturn = true;

Viewi\App::init([
    PageEngine::SOURCE_DIR => 'path/to/your/components', // Location of components source code
    PageEngine::SERVER_BUILD_DIR => 'server/build/path', // Target directory of compiled php components
    PageEngine::PUBLIC_BUILD_DIR => 'public/build/path', // Target directory of compiled public assets (javascripts, etc.)
    PageEngine::DEV_MODE => true, // true if you are in developing mode. All components will be compiled as soon as request occures
    PageEngine::RETURN_OUTPUT => $renderReturn // true if you want to render into variable, otherwise - echo output
]);
$page = Viewi\App::getEngine();

// render selected component, for example HomeComponent
$html = $page->render(HomeComponent::class);

```

# Supported features

## Render property
`<div>$myVar</div>` or `<div>{$myVar}</div>`. In case of object or array use `{}` `<div>{$user->Name}</div>`. All values are automatically encoded.

## Render method\`s call result with {expression} syntax
`<div>{method()}</div>`. All values are automatically encoded.

## Render raw html with {{expression}} syntax
`<div>{{$raw}}</div>`

## Render component
Put component class name `<ComponentName></ComponentName>` (without namespace) as a tag in your template.
For example:

component: *app/HomeLink.php*
```php
<?php

use Viewi\BaseComponent;

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

## Dynamic attributes
You can use dynamic attributes. Please note: if you have the same attribute already they will not be merged into one.
```php
//...
class HomeComponent extends BaseComponent
{
    public string $attribute = 'my-attribute';
//...
```
template: *app/home.html*
```html
<h1>$title</h1>
<div $attribute="some-value"></div>
```

## Dynamic components

You can use a component defined in variable, just make sure that it exists.

```php
//...
class HomeComponent extends BaseComponent
{
    public string $currentPage = 'BlogComponent';
//...
```
template: *app/home.html*
```html
<h1>$title</h1>
<!-- will render BlogComponent here -->
<$currentPage></$currentPage>
```

## Slots
You can pass content into a component which will be rendered instead of `<slot>` tag. Also you can specify default content (optional).
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

## Named slots

You can also have named slots using `<slot name="top">` tag with name attribute. To specify content for named slot you should use `<slotContent name="top">` tag with name attribute. Slot without a name attribute bacomes slot by default and any content outside `<slotContent..` tag becomes content for default slot `<slot>` (without name attribute).

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

## If statement
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

## Foreach

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

## If and foreach combinations

You can have `if` and `foreach` together, but the order matters: 
This will check `if` condition first, and if it's true will execute `foreach`
```php
<div if="$condition" foreach="$array as $item"...`
```
And this will run `foreach` first and then check `if` condition for each item
```php
<div foreach="$array as $item" if="$item->active"...`
```

## Boolean attributes

If html attribute is boolean you can pass condition into attribute value, and it will render attribute based on that condition. List of boolean attributes: `async` `autofocus` `autoplay` `checked` `controls` `default` `defer` `disabled` `formnovalidate` `hidden` `ismap` `itemscope` `loop` `multiple` `muted` `nomodule` `novalidate` `open` `readonly` `required` `reversed` `selected`

component: *app/HomeLink.php*
```php
//...
class HomeLink extends BaseComponent
{
    public bool $isDisabled = true;
    public bool $checked = false;
//...
```
template: *app/HomeLink.html*
```html
<button disabled="$isDisabled">Send</button>
<input type="checkbox" value="1" checked="$checked" />
```
*Result:*
```html
<button disabled="disabled">Send</button>
<input type="checkbox" value="1" />
```

## Conditional attributes

Conditional attributes help you to simplify using attributes based on conditions.
For example, instead of using `$condition ? 'one' : 'two'` like here
```html
<div class="panel {$selected ? 'show' : ''}"></div>
```
you can use `class.show="$selected"` like here
```html
<div class="panel" class.show="$selected"></div>
```
You can have as many attributes as you want, all of them will be merged during render.


## Passing inputs into component

You can pass any data into a component, data will be assigned to component's public properties.
component: *app/HomeLink.php*
```php
//...
class HomeLink extends BaseComponent
{
    public string $url;
    public string $title;
    public bool $active;
//...
```
template: *app/HomeLink.html*
```html
<a href="$url" class.active="$active">$title</a>
```
template: *app/home.html*
```html
<h1>$title</h1>
<HomeLink title="My title" url="/" active="true"></HomeLink>
<HomeLink title="$title" url="/blog" active="false"></HomeLink>
```
*Result:*
```html
<h1>My awesome application</h1>
<a href="/" class="active">My title</a>
<a href="/blog">My awesome application</a>
```

## Template

You can use tag `<template>` to group elements into one logical entity on one side, and on the other side only `<template>` content will be rendered. Useful when used in combination with `if` or/and `foreach`.

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

## Event Handling

You can use `(eventName)` directive in order to listen to DOM events and run some code when they are occured. The usage is simple:
1. Write `(event)="Method()"` in your html template, for example:
    template: *app/home.html*
    ```html
    <h2>Counter value: $count</h2>
    <button (click)="Increment()">Increment</button>
    ```
2. Define method `Increment` in your component and make sure it's public, for example:
    ```php
    //...
    class HomeComponent extends BaseComponent
    {
        public int $count = 0;

        function Increment($event)
        {
            $this->count++;
        }
    //...
    ```
3. And now component will update html accordingly each time you click on the button


## DI

Dependency injection. To make DI work in your component you must to declare `__init` method, and all required arguments will be resolved automatically during the render. If it's a service you can use `__construct` as well. `__init` has higher priority than `__construct`.
```php
//...
class HomeLink extends BaseComponent
{
    function __init(
        NotificationService $notificationService,
        HttpClientService $http,
        string $name,
        ?int $cost,
        ?NotificationService $ns,
        ?float $f = 3,
        ?array $test = [5, 6]
    ) {
//...
```
You can pass any inputs here and DI will try to resolve as much as possible based on the b type of argument, default values, etc. It is required from you to write dependencies correctly and avoid recursion. All services will be shared between all components during render, all child components will be created every time as new.

# Advanced

## Data fetching

You have `HttpClient` at your command which allows you to fetch data from certain url. If component is rendering on the server side it will invoke Router and Controller internally in order to retrieve data (Adapters for Laravel and Symfony are in progress) and if it's a front end it will make ajax request to the same url. As a result you will get the same data no matter at which side it was.

```php
use DevApp\PostModel;
use Viewi\BaseComponent;
use Viewi\Common\HttpClient;

class PostPage extends BaseComponent
{
    public ?PostModel $post = null;

    function __init(int $postId, HttpClient $http)
    {
        $http->get("/api/posts/$postId")->then(
            function (PostModel $post) {
                $this->post = $post;
            },
            function ($error) {
                // display error
            }
        );
    }
}
```

## Routing params

Imagine that you have a route like this `'/post/{postId}'` that is registered to PostPage component. And you need to get `{postId}`. Well, luckily for you, Viewi engine will inject this as an input argument into your component (see code snippet above).

