# Leaf

Leaf is a PHP micro framework that helps you create clean, simple but powerful web apps and APIs quickly

## Installation

It's recommended that you use [Composer](https://getcomposer.org/) to install Leaf.

```bash
$ composer require leafs/leaf
```

This will install Leaf in your project directory.

## Basic Usage
This is a simple demmonstration of Leaf's simplicity.
After [installing](#installation) Leaf, create an _index.php_ file.

```php
<?php
require __DIR__ . '/../vendor/autoload.php';

// Instantiate Leaf
$leaf = new Leaf\Core\Leaf();
$request = new Leaf\Core\Http\Request();
$response = new Leaf\Core\Http\Response();

// Add routes
$leaf->get('/', function () use($response) {
   echo $response->renderMarkup('<h5>My first Leaf app</h5>');
});

$leaf->post('/users/add', function () use($response, $request) {
    $name = $request->getParam('name');
    echo $response->respond(["message" => $name." has been added"]);
});

$leaf->run();
```

You may quickly test this using the built-in PHP server:
```bash
$ php -S localhost:8000
```

## View Leaf's docs [here](https://leaf-docs.netlify.com/v1.3.0)
