# [Draft] Architecture overview for developers

## Component definition

In Viewi, component is a combination of files:

`MyComponent.php` and `MyComponent.html` (both files should have the same base name)

Optionally, you can have:

`MyComponent.js` - manually created javascript file for front-end. Otherwise it will be translated from the PHP file.

`MyComponent.partial.js` = partial code for front-end in addition for translated from PHP file.

### Class definition

`MyComponent.php` file should contain a definition for class that is derived from `BaseComponent` class

```php
use Viewi\BaseComponent;

class MyComponent extends BaseComponent
{
    // properties and methods
}
```

### Template file

`MyComponent.html` contains HTML content mixed with PHP code for rendering component's properties, handling events, conditional and foreach directives, etc. 

```html
<Layout title="$title">
    <Container>
        <h1>Welcome to Viewi UI</h1>
        <div class="pa-2">
            Clicked $clickCount
        </div>
        <Button (click)="onClick($event)" color="primary">
            Increase
            <Icon name="mdi-checkbox-marked-circle" position="right" />
        </Button>
    </Container>
</Layout>
```

## Compiling

Entry point:

```php
class PageEngine
...
function compile(string $initialComponent = null): void
```

### Step 1 - Translating to js

```php
class PageEngine
...
function compileToJs(ReflectionClass $reflectionClass): void
```

It converts PHP code into javascript using `JsTranslator` class.

Additionally:

- it collects a list of dependencies (classes from `using` statement, that need to be converted as well).
- it collects a list of includes for build-in PHP functions from `locutus` project - JS implementation for PHP functions. It includes the implementation only if it is used in the source code.
- it collects variable paths tree for driving reactivity on front-end side. For example:
```
Global -> MyClass -> Property1
                  -> Method1 -> Property1 
                  // Method1 uses Property1, 
                  // in that way {Method1()} in your component will stay reactive
                  // event if Property1 gets updated.
                  // No overheads.
```

### Step 2 - Parsing component templates

The engine is searching for components inside of `SOURCE_DIR` folder. That folder is specified in the config.

For each discovered component

