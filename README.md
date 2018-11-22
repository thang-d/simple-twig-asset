# Simple Twig Asset

This is a simple twig asset extension for Slim 3.x helper manage your resource in the Twig templating component. When you want to change the public resource, with the traditional way, you must change URL resource one by one for each resource in each template `.twig`. With TwigAsset, you don't worry about that, with simple way, you can change all of the resources to new URL. Besides, it's helping you against browser cached resource with a set suffix at the end of resource URL, all of that is automated. And so more it can do.

## Install

Via [Composer](https://getcomposer.org/)

```bash
$ composer require lovecoding/twig-asset
```

Requires Slim Framework 3.x and PHP 7.0.0 or newer.

## Usage

### Setup

* In php

```php
// Create Slim app
$app = new \Slim\App();

// Fetch DI Container
$container = $app->getContainer();

// Register Twig View helper
$container['view'] = function ($c) {
    $view = new \Slim\Views\Twig('path/to/templates');

    // Instantiate and add Slim specific extension
    $router = $c->get('router');
    $uri = \Slim\Http\Uri::createFromEnvironment(new \Slim\Http\Environment($_SERVER));
    $view->addExtension(new \Slim\Views\TwigExtension($router, $uri));

    $assetManager = new LoveCoding\TwigAsset\TwigAssetManagement([
        'verion' => '1'
    ]);
    $assetManager->addPath('css', '/css');
    $assetManager->addPath('img', '/images');
    $assetManager->addPath('js', '/js');
    $view->addExtension($assetManager->getAssetExtension());

    return $view;
};

// Define named route
$app->get('/home', function ($request, $response, $args) {
    return $this->view->render($response, 'home.html');
});

// Run app
$app->run();
```

* In `home.html`

In this version `TwigAsset` only provide one function to your Twig templates:

    - `asset(path, namespace)` - returns the real URL resource from your `path`.
        - path - your static `path`.
        - namespace - group the resource to only one name, the url will be shorted.

```html
<!DOCTYPE html>
<html>
<head>
    <!-- absolute path -->
    <!-- result: href="/path/to/css1.css?v=1" -->
    <link rel="stylesheet" type="text/css" href="{{ asset('/path/to/css1.css') }}">

    <!-- relative path with prefix -->
    <!-- result: href="/css/path/to/css2.css?v=1" -->
    <link rel="stylesheet" type="text/css" href="{{ asset('path/to/css3.css', 'css') }}">
</head>
<body>
    <div>
        <!-- absolute path -->
        <!-- result: href="/path/to/images.png?v=1" -->
        <img src="{{ asset('/path/to/images.png') }}">

        <!-- relative path with prefix -->
        <!-- result: href="/images/path/to/images.jpg?v=1" -->
        <img src="{{ asset('path/to/images.jpg', 'img') }}">
    </div>
</body>

<!-- absolute path -->
<!-- result: href="/path/to/js2.js?v=1" -->
<script type="text/javascript" src="{{ asset('/path/to/js1.js') }}"></script>

<!-- relative path with prefix -->
<!-- result: href="js/path/to/js2.js?v=1" -->
<script type="text/javascript" src="{{ asset('path/to/js2.js', 'js') }}"></script>
</html>
```

### Example settings

- Example 1

```php
$settings = [
    'version' => '1', // version will be setting in the end of asset url `css.css?(version_format here)`
    'version_format' => '%s?v=%s',
];

// in the html if you call asset('/image.png')
// the result: /v1/image.png?v=1
```

- Example 2

```php
$settings = [
    'version' => 'v1',
    'version_format' => '%2$s/%1$s',
];

// in the html if you call asset('/image.png')
// the result: /v1/image.png
```

- Example 3

```json
// rev-manifest.json
{
    "css/app.css": "build/css/app.b916426ea1d10021f3f17ce8031f93c2.css",
    "js/app.js": "build/js/app.13630905267b809161e71d0f8a0c017b.js",
    "...": "..."
}
```

```php
$settings = [
    'json_manifest' => __DIR__.'/rev-manifest.json'
];

// in the html if you call asset('css/app.css')
// the result: css/app.b916426ea1d10021f3f17ce8031f93c2.css
```

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.