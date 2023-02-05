# NO_ROUTER option

### Viewi will not intercept your link clicks and will not handle page reloads.

Useful when:

- Using Viewi with MFE (micro front-end) architecture.
- Using Viewi without Router module on static pages with server side reloads.
- Using Viewi as a side application in addition to your main javascript application.

## Config

```php
PageEngine::NO_ROUTER => true
```

Example:

`config.php`
```php
<?php

use Viewi\PageEngine;

return [
    PageEngine::SOURCE_DIR => __DIR__ . '/Components',
    PageEngine::SERVER_BUILD_DIR =>  __DIR__ . '/build',
    PageEngine::PUBLIC_ROOT_DIR => __DIR__ . '/../public/',
    PageEngine::NO_ROUTER => true, // <- here
    PageEngine::DEV_MODE => true,
    PageEngine::RETURN_OUTPUT => true,
    PageEngine::COMBINE_JS => true,
    PageEngine::MINIFY => true
];
```

## Usage

### Include `Viewi` script on your page

```html
<body>
    ...
    <?= App::getScriptsHtmlCode() ?>
</body>

</html>
```

Optionally you can use `getScriptsArray` method to get list of scripts to include it in your own manner, like in MFE using `includeScript(path).then(...)` or similar.

```php
App::getScriptsArray(): array
```

### Define the places on your page where do you want to render Viewi components

```html
<body>
    <div id="sidebar">
    </div>
    <div id="content">
        <div>
            <h1>Testing Viewi Partial render with custom target</h1>
            <p>
                Viewi application should be rendered below.
            </p>
        </div>
        <div id="todo-app">
        </div>
        <div id="counter-app">
        </div>
    </div>
    <?= App::getScriptsHtmlCode() ?>
</body>
```

### Run Viewi `runComponent` method to render your components

Get viewiApp instance:

```js
var viewiApp = viewiBring('viewiApp');
```

Call `runComponent` method:

```js
viewiApp.runComponent(componentName, selector, params = null);
```

For example:

```js
// rendering Viewi component into DOM node
function renderViewiComponents() {
    // use `viewiBring` method to get viewiApp 
    var viewiApp = viewiBring('viewiApp');
    // call `runComponent(componentName, selector, params = null)` method
    // render todo app
    viewiApp.runComponent('TodoApp', '#todo-app');
    // render menu
    viewiApp.runComponent('MenuBar', '#sidebar');
    // render counter app
    viewiApp.runComponent('Counter', '#counter-app');
}

// wait for Viewi scripts
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', renderViewiComponents);
} else {
    renderViewiComponents();
}
```

Or if you use it within js:

*Please note, Viewi does not provide `includeScript` method. It is anticipated that you have it (or similar) already as part of your code base*

```js
const viewiScripts = <?= json_encode(App::getScriptsHtmlCode()); ?>;
Promise.all(viewiScripts.map(script => includeScript(script)))
    .then(() => {
        const viewiApp = viewiBring('viewiApp');
        // render todo app
        viewiApp.runComponent('TodoApp', '#todo-app');
        // render menu
        viewiApp.runComponent('MenuBar', '#sidebar');
        // render counter app
        viewiApp.runComponent('Counter', '#counter-app');
    });
```