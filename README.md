<p align="center"><a href="https://github.com/viewi/viewi#logo"><img src="https://viewi.net/logo.svg" alt="Viewi" height="180"/></a></p>
<h1 align="center">Viewi</h1>
<h2 align="center">Powerful tool for building full-stack and completely reactive user interfaces using PHP</h2>

Imagine Vue js or Angular but in PHP world with the similar user-friendly html templates and components, the application that acts like frontend framework and backend template engine at the same time and renders identical contents on both sides. And you don't even need Node js. Isn't it awesome ?

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

### Stage: PoC

Not production ready yet, as it's in development and still needs a lot of work

#### *Requirements: php 7.4 or 8.0+*

Supported versions will be expanded  in the future

Get started
-----------
[Quick Start](https://viewi.net/docs)

Features
----------------
- Server side rendering (SSR)
- Perfect page load score
- Client side rendering (CSR)
- SEO friendly
- No "HTML over the wire".
- Reactive application
- Easy to use
- Simple templates syntax, mix of HTML and PHP
- The same code for backend and frontend, without need to duplicate the logic in javascript.
- Web, mobile, desktop support (planned)
- Does not require Node js

## How does it work ?

Under the hood Viewi translates view components into the javascript, and uses it for front end reactive application.

## Documentation

[https://viewi.net/docs](https://viewi.net/docs)

## Tests

Got to `tests` folder
Run `php test.php backend`

Support
--------

We all have full-time jobs and dedicate to this project our free time, and we would really appreciate Your help of any kind. If you like what we are creating here and want us to spend more time on this, please consider to support:

 - Give us a star⭐.
 - Follow us on [Twitter](https://twitter.com/viewiphp).
 - Contribute by sending pull requests.
 - Any other ideas or proposals ? Please mail me voitovych.ivan.v@gmail.com.
 - Feel welcome to share this project with your friends.


License
--------

MIT License

Copyright (c) 2020-present Ivan Voitovych

Please see [LICENSE](/LICENSE) for license text


Legal
------

By submitting a Pull Request, you disallow any rights or claims to any changes submitted to the Viewi project and assign the copyright of those changes to Ivan Voitovych.

If you cannot or do not want to reassign those rights (your employment contract for your employer may not allow this), you should not submit a PR. Open an issue and someone else can do the work.

This is a legal way of saying "If you submit a PR to us, that code becomes ours". 99.9% of the time that's what you intend anyways; we hope it doesn't scare you away from contributing.
