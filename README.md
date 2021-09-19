<!-- markdownlint-disable no-inline-html -->
<p align="center">
  <br><br>
  <img src="https://leaf-docs.netlify.app/images/logo.png" height="100"/>
  <h1 align="center">Leaf PHP Framework</h1>
  <br><br>
</p>

# Leaf PHP

[![Latest Stable Version](https://poser.pugx.org/leafs/leaf/v/stable)](https://packagist.org/packages/leafs/leaf)
[![Total Downloads](https://poser.pugx.org/leafs/leaf/downloads)](https://packagist.org/packages/leafs/leaf)
[![License](https://poser.pugx.org/leafs/leaf/license)](https://packagist.org/packages/leafs/leaf)

Leaf is a PHP framework that helps you create clean, simple but powerful web apps and APIs quickly and easily. Leaf introduces a cleaner and much simpler structure to the PHP language while maintaining it's flexibility. With a simple structure and a shallow learning curve, it's an excellent way to rapidly build powerful and high performant web apps and APIs.

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
require __DIR__ . "vendor/autoload.php";

$app = new Leaf\App;
$auth = new Leaf\Auth;

$auth->connect("host", "user", "pass", "db name");

// Base example
$app->get("/", function() use($app) {
  $app->response()->json([
    "message" => "Welcome!"
  ]);
});

// Full login example
$app->post("/auth/login", function() use($app, $auth) {
  $credentials = $app->request()->get(["username", "password"]);

  $user = $auth->login("users", $credentials, [
    "username" => ["username", "max:15"],
    "password" => ["text", "NoSpaces", "min:8"],
  ]);

  if (!$user) {
    $app->response()->throwErr($auth->errors());
  }

  $app->response()->json($user);
});

$app->run();
```

You may quickly test this using the built-in PHP server:

```bash
php -S localhost:8000
```

**You can view the full documentation [here](https://leafphp.netlify.app/#/)**

## Working With MVC

Leaf has recently added a new package to it's collection: LeafMVC.
It's an MVC framework built with this package at it's core that let's you create clean, simple but powerful web applications and APIs quickly and easily.

Checkout LeafMVC [here](https://github.com/leafsphp/leafMVC)

## Working with API

Leaf also added a simple framework constructed in an MVCish way, but without the View layer purposely for creating APIs and Libraries. Leaf terms this construct as MRRC(Model Request Response Controller😅😅😅). This let's you seperate API logic, data and "views"(request and response) just like how it's done in MVC.

Checkout the LeafAPI package [here](https://github.com/leafsphp/leafAPI)

## Skeleton

Skeleton is the latest package included in the Leaf family. Skeleton is a customizable and simple to use boilerplate powered by Leaf. Skeleton gives you the power of other setups like Leaf MVC without the restrictions of those full blown frameworks. [Use and contribute](https://github.com/leafsphp/skeleton) to Skeleton

Of course, with this core package, you can build your app in any way that you wish to as Leaf contains all the required functionality to do so

## Our contributors

And to all our contributors, we love you all ❤️

<a href="https://github.com/leafsphp/fetch/graphs/contributors" target="_blank"><img src="https://opencollective.com/leafphp/contributors.svg?width=890" /></a>

## View Leaf's docs [here](https://leafphp.netlify.app/#/)

Built with ❤ by [**Mychi Darko**](https://mychi.netlify.app)
