## Page Engine

### Powerfull view engine for your application


**Code:**
file: *app/home.php*
```
<?php

use Vo\BaseComponent;

class AppComponent extends BaseComponent
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
file: *app/home.html*
```
<h1>$title</h1>
<p>Name: {getFullName()}</p>
<p>Occupation: {getOccupation()}</p>
```
**Result:**
```
<h1>My awesome application</h1>
<p>Name: Jhon Doe</p>
<p>Occupation: Web developer</p>
```

### How to use
```
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

// render selected component, for example AppComponent
$html = $page->render(AppComponent::class);

```

### Supported features

|Command    |How to use |
|---        |---        |
|           |           |