<!-- markdownlint-disable no-inline-html -->
<p align="center">
    <br><br>
    <img src="https://leaf-docs.netlify.app/images/logo.png" height="100"/>
    <h1 align="center">Leaf PHP Framework</h1>
    <h3 align="center">Special Edition: <b>Lucky Charm🍀</b></h3>
    <br>
    <br><br><br>
</p>

[![Latest Stable Version](https://poser.pugx.org/leafs/leaf/v/stable)](https://packagist.org/packages/leafs/leaf)
[![Total Downloads](https://poser.pugx.org/leafs/leaf/downloads)](https://packagist.org/packages/leafs/leaf)
[![License](https://poser.pugx.org/leafs/leaf/license)](https://packagist.org/packages/leafs/leaf)

# Leaf 2.3 (Lucky Charm🍀)

Leaf is a PHP framework that helps you create clean, simple but powerful web apps and APIs easily.

## Installation

You can easily install Leaf using [Composer](https://getcomposer.org/).

```bash
composer require leafs/leaf
```

This will install Leaf in your project directory.

## Basic Usage

This is a simple demonstration of Leaf's simplicity.
After [installing](#installation) Leaf, create an _index.php_ file.

```php
<?php
require __DIR__ . 'vendor/autoload.php';

// Instantiate Leaf
$app = new Leaf\App;

// Add routes
$app->get('/', function () use($app) {
   $app->response->respond("My first Leaf app");
});

$app->post('/users/add', function () use($app) {
    $name = $app->request->get('name');
    $app->response->respond("Your name is $name");
});

// Don't forget to call app run
$app->run();
```

You can view the full documentation [here](https://leafphp.netlify.app/#/)

You may quickly test this using the built-in PHP server:

```bash
php -S localhost:8000
```

## Working With MVC

Leaf has recently added a new package to it's collection: LeafMVC.
It's an MVC framework built with this package at it's core that let's you create clean, simple but powerful web applications and APIs quickly and easily.

Ckeckout LeafMVC [here](https://github.com/leafsphp/leafMVC)

## Working with API

Leaf also added a simple framework constructed in an MVCish way, but without the View layer purposely for creating APIs and Libraries. Leaf terms this construct as MRRC(Model Request Response Controller😅😅😅). This let's you seperate API logic, data and "views"(request and response) just like how it's done in MVC.

Checkout the LeafAPI package [here](https://github.com/leafsphp/leafAPI)

## Skeleton

Skeleton is the latest package included in the Leaf family. Skeleton is a customizable and simple to use boilerplate powered by Leaf. Skeleton gives you the power of other setups like Leaf MVC without the restrictions of those full blown frameworks. [Use and contribute](https://github.com/leafsphp/skeleton) to Skeleton

Of course, with this core package, you can build your app in any way that you wish to as Leaf contains all the required functionality to do so

## View Leaf's docs [here](https://leafphp.netlify.app/#/)

## What's changed in Lucky Charm

- Bug fixes on most known bugs
- Cleaner and easier to use method
- Smaller size
- Improved performance
- Brand new methods
- Outdated code removals
- Security Updates

[Read the changelog] for more precise information

Built with ❤ by [**Mychi Darko**](//mychi.netlify.app)
