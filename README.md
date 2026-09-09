[![Stand With Ukraine](https://raw.githubusercontent.com/vshymanskyy/StandWithUkraine/main/banner-direct-single.svg)](https://stand-with-ukraine.pp.ua)

<p align="center"><a href="https://github.com/viewi/viewi#logo"><img src="https://viewi.net/logo.svg" alt="Viewi" height="180"/></a></p>
<h1 align="center">Viewi</h1>
<h2 align="center">Unique and efficient front-end framework for PHP</h2>

### Examples

```html
<div>
    <Thumbnail blog="$blog" />
    <a href="{$blog->url}">
        <h3>{$blog->title}</h3>
        <p>{$blog->description}</p>
    </a>
    <LikeButton liked="{$blog->favorite}" (click)="like" />
</div>
```

```php
class Blog extends BaseComponent
{
    public BlogModel $blog;

    public function like()
    {
        $this->blog->favorite = !$this->blog->favorite;
    }
}
```

![Blog](/images/blog.png)


Discover more at [https://viewi.net](https://viewi.net).

## Documentation

[https://viewi.net/docs](https://viewi.net/docs/introduction)

[Discussions (Forum)](https://github.com/viewi/viewi/discussions)


## In production

[Urlicer](https://urlicer.com) is branded short links, QR codes, team workspaces and click
analytics. It runs its entire frontend on Viewi: server-rendered PHP components hydrated in the
browser, with no JavaScript framework. Built and maintained by Viewi's author.


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
 - Any other ideas or proposals? Please mail me contact@viewi.net.
 - Feel welcome to share this project with your friends.


License
--------

Copyright (c) 2020-present Ivan Voitovych

Please see [MIT](/LICENSE) for license text
