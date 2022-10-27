[![Stand With Ukraine](https://raw.githubusercontent.com/vshymanskyy/StandWithUkraine/main/banner-direct-single.svg)](https://stand-with-ukraine.pp.ua)

<p align="center"><a href="https://github.com/viewi/viewi#logo"><img src="https://dev.viewi.net/logo-ua.svg" alt="Viewi" height="180"/></a></p>
<h1 align="center">Viewi</h1>
<h2 align="center">A powerful tool for building full-stack and completely reactive web applications with PHP</h2>

Imagine Vue js or Angular but with similar user-friendly HTML templates and components but written in PHP. The application, which acts like a front-end framework and backend template engine simultaneously, renders identical contents on both sides. And you don't even need Node js. Isn't it awesome?

Works with a limited subset of PHP, but even then, it is still more than enough for building advanced web applications.

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

MIT License

Copyright (c) 2020-present Ivan Voitovych

Please see [LICENSE](/LICENSE) for license text


Legal
------

By submitting a Pull Request, you disallow any rights or claims to any changes submitted to the Viewi project and assign the copyright of those changes to Ivan Voitovych.

If you cannot or do not want to reassign those rights (your employment contract for your employer may not allow this), you should not submit a PR. Open an issue, and someone else can do the work.

This is a legal way of saying, "If you submit a PR to us, that code becomes ours." 99.9% of the time, that's what you intend anyways; we hope it doesn't scare you away from contributing.
