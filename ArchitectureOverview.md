# [Draft] Architecture overview for developers

**Note:** The current Viewi version is `v0.x` and requires much refactoring. This guide is meant to clarify and help you understand the core if you decide to contribute to this project. Hopefully, someday, we will roll out Viewi `v1.x` - a thoroughly tested, much more readable source code and maximum optimized.

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

### Step 2 - Building dependencies (DI)

For each discovered class (every class, not only component) it collects all dependencies:

```php
class PageEngine
...
function buildDependencies(ReflectionClass $reflectionClass): void
...
function getDependencies(ReflectionClass $reflectionClass): array
```

For example:

```php
class TestComponent extends BaseComponent
{
...
    public function __init(int $id, HttpClient $http, SessionInterceptor $session, AuthorizationInterceptor $auth)
    {
```

Will collect:

```php
[
    'id' =>
    [
        'name' => 'int',
        'builtIn' => 1,
    ],
    'http' =>
    [
        'name' => 'HttpClient',
    ],
    'session' =>
    [
        'name' => 'SessionInterceptor',
    ],
    'auth' =>
    [
        'name' => 'AuthorizationInterceptor',
    ],
]
```


### Step 3 - Parsing component templates

The engine is searching for components inside of `SOURCE_DIR` folder. That folder is specified in the config.

For each discovered component it parses the template (.html)

```php
class PageEngine
...
function compileTemplate(ComponentInfo $componentInfo): PageTemplate
```

It returns `PageTemplate` object with `TagItem` tree, which represents the DOM/PHP tree.

to be continue..


