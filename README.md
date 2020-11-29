## Powerful tool for building full-stack and completely reactive user interfaces using PHP

Imagine Vue js or Angular but in PHP world with the similar user-friendly html templates and components, the application that acts like frontend framework and backend template engine at the same time and renders identical contents on both sides. And you don't even need Node js. Isn't it awesome?

### Stage: PoC

Not production ready yet, as it's in development and still needs a lot of work

#### *Requirements: php 7.4+*

Supported versions will be expanded  in the future

Get started
-----------

### Install Viewi

Run:

`composer require viewi/viewi:dev-master`

`vendor/bin/viewi new`

Make sure that script has been generated for you files in one of these folders: `viewi-app/`, `src/ViewiApp/` or your custom folder if you specified it directly.

Check you index.php. Script should generate for your code to include, usually you just need to uncomment generated code. Also don't forget about `vendor/autoload.php`

```php
require __DIR__ . '/vendor/autoload.php';

// Viewi application here, uncomment to use as standalone application
include __DIR__ . '/viewi-app/viewi.php';
Viewi\App::handle();
```

And now just run `php -S localhost:8000` and open your browser at `http://localhost:8000/`. If everything is good you should be able to see Viewi demo application.

Features
----------------
- Server side rendering
- Perfect page load score
- Front end rendering
- Reactive application
- Easy to use
- Simple templates syntax, mix of HTML and PHP
- The same code for backend and frontend, without need to duplicate the logic in javascript.
- Web, mobile, desktop support (planned)
- Does not require Node js

## How does it work ?

Under the hood Viewi translates view components into the javascript, and uses it for front end reactive application.

## Short documentation

[PageEngine](/doc/PageEngine.md)

## Tests

Got to `tests` folder
Run `php test.php backend`

Support
--------

We all have full-time jobs and dedicate to this project our free time for over six months now, and we would really appreciate Your help of any kind. If you like what we are creating here and want us to spend more time on this, please consider to support:

 - Become a backer or sponsor via [Patreon](https://www.patreon.com/ivanvoitovych).
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
