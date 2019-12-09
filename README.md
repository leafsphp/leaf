<p align="center">
    <br><br>
    <img src="https://leaf-docs.netlify.com/images/logo.png" height="100"/>
    <h1 align="center">Leaf PHP Framework</h1>
    <br>
    <br><br><br>
</p>


[![Latest Stable Version](https://poser.pugx.org/leafs/leaf/v/stable)](https://packagist.org/packages/leafs/leaf)
[![Total Downloads](https://poser.pugx.org/leafs/leaf/downloads)](https://packagist.org/packages/leafs/leaf)
[![License](https://poser.pugx.org/leafs/leaf/license)](https://packagist.org/packages/leafs/leaf)


# Leaf
Leaf is a PHP micro framework that helps you create clean, simple but powerful web apps and APIs quickly.

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
$leaf = new Leaf\Core\Leaf;
$request = new Leaf\Core\Http\Request;
$response = new Leaf\Core\Http\Response;

// Add routes
$leaf->get('/', function () use($response) {
   $response->renderMarkup('<h5>My first Leaf app</h5>');
});

$leaf->post('/users/add', function () use($response, $request) {
    $name = $request->get('name');
    $response->respond(["message" => $name." has been added"]);
});

$leaf->run();
```

You may quickly test this using the built-in PHP server:
```bash
$ php -S localhost:8000
```

# Working With MVC
Leaf has recently added a new package to it's collection: LeafMVC.
It's an MVC framework built with this package at it's core that let's you create clean, simple but powerful web applications and APIs quickly and easily.


Ckeckout LeafMVC [here](https://github.com/leafsphp/leafMVC)


# Working with API
Leaf also added a simple framework constructed in an MVCish way, but without the View layer purposely for creating APIs and Libraries. Leaf terms this construct as MRRC(Model Request Response Controller😅😅😅). This let's you seperate API logic, data and "views"(request and response) just like how it's done in MVC. 


Checkout the LeafAPI package [here](https://github.com/leafsphp/leafAPI)


Of course, with this core package, you can build your app in any way that you wish to as Leaf contains all the required functionality to do so


## View Leaf's docs [here](https://leaf-docs.netlify.com/v1.3.0)
