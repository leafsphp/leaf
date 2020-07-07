<!-- markdownlint-disable no-inline-html -->
<p align="center">
    <br><br>
    <img src="https://leaf-docs.netlify.app/images/logo.png" height="100"/>
    <h1 align="center">Leaf PHP Framework</h1>
    <br>
    <br><br><br>
</p>

[![Latest Stable Version](https://poser.pugx.org/leafs/leaf/v/stable)](https://packagist.org/packages/leafs/leaf)
[![Total Downloads](https://poser.pugx.org/leafs/leaf/downloads)](https://packagist.org/packages/leafs/leaf)
[![License](https://poser.pugx.org/leafs/leaf/license)](https://packagist.org/packages/leafs/leaf)

# Leaf 2.1

Leaf is a PHP micro framework that helps you create clean, simple but powerful web apps and APIs quickly.

## Installation

It's recommended that you use [Composer](https://getcomposer.org/) to install Leaf.

```bash
composer require leafs/leaf
```

This will install Leaf in your project directory.

If you don't want this method, you can simply clone this repo and run `composer install` to download any dependencies. You can then start your server and build your leaf app.

## Basic Usage

This is a simple demonstration of Leaf's simplicity.
After [installing](#installation) Leaf, create an _index.php_ file.

```php
<?php
require __DIR__ . 'vendor/autoload.php';

// Instantiate Leaf
$app = new Leaf\App;

// In v2.0, the request and response objects are directly tied to the Leaf Object,
// so you don't have to instanciate them if you don't want to

// Example get route
$app->get('/', function () use($app) {
  // since the response object is directly tied to the leaf instance
  $app->response->renderMarkup('<h5>My first Leaf app</h5>');
});

$app->post('/users/add', function () use($app) {
  $name = $app->request->get('name');
  $app->response->respond(["message" => $name." has been added"]);
});

// Don't forget to call leaf run
$app->run();
```

You can view the full documentation [here](https://leafphp.netlify.com/#/)

You may quickly test this using the built-in PHP server:

```bash
php -S localhost:8000
```

# Working With MVC

Although leaf on it's own isn't an MVC framework, it contains useful tools and packages which allow you to use Leaf as any other MVC framework.

If however, you want an already built MVC setup with scaffolding and a whole lot of other amazing features, you can try out [Leaf API](https://leafphp.netlify.app/#/leaf-api) or [Leaf MVC](https://leafmvc.netlify.app/).

## Leaf API

Leaf API is a lightweight PHP MVC framework for rapid API development. Leaf API serves as minimal MVC wrapper around Leaf PHP Framework which allows you to use Leaf in an MVC environment. It also comes along with a bunch of handy tools which can speed up your development by leagues🙂

[Read the docs](https://leafphp.netlify.app/#/leaf-api) and [Contribute on github](https://github.com/leafsphp/leafAPI).
