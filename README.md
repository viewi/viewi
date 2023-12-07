[![Stand With Ukraine](https://raw.githubusercontent.com/vshymanskyy/StandWithUkraine/main/banner-direct-single.svg)](https://stand-with-ukraine.pp.ua)

<p align="center"><a href="https://github.com/viewi/viewi#logo"><img src="https://dev.viewi.net/logo.svg" alt="Viewi" height="180"/></a></p>
<h1 align="center">Viewi V2 [WIP]</h1>
<h2 align="center">A powerful tool for building full-stack and completely reactive web applications with PHP</h2>


<h3 align="center"><a href="https://github.com/viewi/viewi/tree/v1">Click here for Viewi v1</a></h3>

Full-stack UI framework for PHP with reactivity on front-end and efficient SSR.

Viewi is not about avoiding javascript, it's about integration them both for mutual benefits.

Front-end framework designed for PHP.

How? - by transpiling PHP/HTML code into javacript to fuel front-end.

Transpiling works with a limited subset of PHP (not everything can be converted into JavaScript), but even then, it is still more than enough for building advanced web applications. And if you need more custom JS - there is a way to inject it.

Viewi takes your PHP components and converts them into JavaScript.

The flow:

- User opens URL in the browser.
- Viewi generates SEO friendly HTML page based on your components.
- Browser receives HTML page with Viewi scripts included.
- Viewi JS framework runs hydration process (creates events, makes page alive and reactive, like any other JS framework whould).
- From now on user can interact with the page without requesting new contents from the server.
- When navigating by clicking on links Viewi JS will render the page without making a request to your server using front-end routing.
- API data can be requested with built-in Http Client.

More description is coming.

Testing
--------

#### Run tests

All tests:

`php vendor/bin/codecept run`

Unit tests:

`php vendor/bin/codecept run Unit`

Specific test:

`php vendor/bin/codecept run Unit JsTranspilerTest`


#### Create test

`php vendor/bin/codecept generate:test Unit JsTranspiler`


Support
--------

We all have full-time jobs and dedicate our free time to this project, and we would appreciate Your help of any kind. If you like what we are creating here and want us to spend more time on this, please consider supporting:

 - Give us a star⭐.
 - Support me on [buymeacoffee](https://www.buymeacoffee.com/ivan.v)
 - Follow us on [Twitter](https://twitter.com/viewiphp).
 - Contribute by sending pull requests.
 - Any other ideas or proposals? Please mail me voitovych.ivan.v@gmail.com.
 - Feel welcome to share this project with your friends.


License
--------

Copyright (c) 2020-present Ivan Voitovych

Please see [MIT](/LICENSE) for license text


### Viewi v1:

Short example:
--------
`Counter.php`
```php
<?php

namespace Application\Components\Views\Demo\SimpleComponent;

use Viewi\BaseComponent;

class Counter extends BaseComponent
{
    public int $count = 0;

    public function increment()
    {
        $this->count++;
    }
}
```
`Counter.html` 
```html
<button (click)="increment()">Clicked $count times.</button>
```
`Use it as a tag`:
```html
<Counter />
```
[Live demo](https://viewi.net/)

[UI Components](https://ui.viewi.net/)

#### *Requirements: php ^7.4 || ^8.0*

Get started
-----------
[Quick Start](https://viewi.net/docs)

Features
----------------
- Server-side rendering (SSR)
- Perfect page load score
- Client-side rendering (CSR)
- SEO friendly
- No "HTML over the wire."
- Reactive application
- Easy to use
- Simple templates syntax, a mix of HTML and PHP
- Generates javascript code for you
- Web, mobile, desktop applications support (planned)
- Does not require Node js

## How does it work?

Under the hood, Viewi translates view components into javascript and uses it for a reactive front-end application.

## Documentation

[https://viewi.net/docs](https://viewi.net/docs)

[Discussions (Forum)](https://github.com/viewi/viewi/discussions)

[Frameworks Integration](https://viewi.net/docs/integrations)

## Tests

Got to `tests` folder

Run `php test.php backend`
