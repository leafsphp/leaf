<!-- markdownlint-disable no-inline-html -->
<p align="center">
  <br><br>
  <img src="https://leafphp.dev/logo-circle.png" height="100"/>
  <br>
</p>

<h1 align="center">Leaf PHP</h1>

<p align="center">
  <a href="https://packagist.org/packages/leafs/leaf"
    ><img
      src="https://poser.pugx.org/leafs/leaf/v/stable"
      alt="Latest Stable Version"
  /></a>
  <a href="https://packagist.org/packages/leafs/leaf"
    ><img
      src="https://poser.pugx.org/leafs/leaf/downloads"
      alt="Total Downloads"
  /></a>
  <a href="https://packagist.org/packages/leafs/leaf"
    ><img
      src="https://poser.pugx.org/leafs/leaf/license"
      alt="License"
  /></a>
</p>
<br />
<br />

Leaf is a slim and lightweight PHP framework focused on developer experience, usability, and high-performance code. It introduces a cleaner and much simpler structure to the PHP language while maintaining it's flexibility. With a simple structure and a shallow learning curve, it's an excellent way to rapidly build powerful and high performant web apps and APIs.

## Basic Usage

After [installing](#installation) Leaf, create an _index.php_ file.

```php
<?php

require __DIR__ . '/vendor/autoload.php';

app()->get('/', function () {
  response()->json([
    'message' => 'Hello World!'
  ]);
});

app()->run();
```

You may quickly test this using the Leaf CLI:

```bash
leaf serve
```

Or with the built-in PHP server:

```bash
php -S localhost:5500
```

## Why Leaf?

When it comes to building web applications, there are numerous tools and frameworks at your disposal. Nevertheless, we are convinced that Leaf is the optimal selection for developing powerful, web applications and APIs.

While PHP frameworks speed up web development, they come with challenges like a steep learning curve, potential performance overhead, and stricter code maintenance. They can be rigid, limiting flexibility, and often tie you to a specific ecosystem, making it hard to use unsupported packages. Additionally, frameworks may introduce unused code, leading to bloat and reduced performance.

Leaf addresses these challenges with an easy learning curve, making it accessible to both beginners and experienced devs. It is lightweight, and boosts developer productivity by simplifying usage with global functions.

Beyond this, Leaf is modular, allowing developers to install only necessary features while maintaining compatibility with other libraries and frameworks. Additionally, Leaf is scalable, working seamlessly from development to production with minimal configuration.

## Installation

You can create a new Leaf app using the [Leaf CLI](https://leafphp.dev/docs/cli/)

```bash
leaf create <project-name> --basic
```

`<project-name>` is the name of your project

You can also use [Composer](https://getcomposer.org/) to install Leaf 3 in your project quickly.

```bash
composer require leafs/leaf
```

## ✈️ The Leaf Ecosystem (Libs & Frameworks)

| Project                                          | Status                                                                                                                                                                                                                                         | Description                                            |
| ------------------------------------------------ | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | ------------------------------------------------------ |
| [leaf](https://github.com/leafsphp/leaf)         | [![Latest Stable Version](https://poser.pugx.org/leafs/leaf/v/stable)](https://packagist.org/packages/leafs/leaf) [![Total Downloads](https://poser.pugx.org/leafs/leaf/downloads)](https://packagist.org/packages/leafs/leaf)                 | Create websites and APIs quickly                       |
| [leafmvc](https://github.com/leafsphp/leafmvc)   | [![Latest Stable Version](https://poser.pugx.org/leafs/mvc/v/stable)](https://packagist.org/packages/leafs/mvc) [![Total Downloads](https://poser.pugx.org/leafs/mvc/downloads)](https://packagist.org/packages/leafs/mvc)                     | An MVC wrapper for leaf      |
| [cli](https://github.com/leafsphp/cli)           | [![Latest Stable Version](https://poser.pugx.org/leafs/cli/v/stable)](https://packagist.org/packages/leafs/cli) [![Total Downloads](https://poser.pugx.org/leafs/cli/downloads)](https://packagist.org/packages/leafs/cli)                     | CLI for creating & interacting with your leaf apps     |

You can find a full list of all modules on the [modules documentation](https://leafphp.dev/modules/)

## 💬 Stay In Touch

- [Twitter](https://twitter.com/leafphp)
- [Join the forum](https://github.com/leafsphp/leaf/discussions/37)
- [Chat on discord](https://discord.com/invite/Pkrm9NJPE3)

## 📓 Learning Leaf PHP

- Leaf has a very easy to understand [documentation](https://leafphp.dev) which contains information on all operations in Leaf.
- You can also check out our [youtube channel](https://www.youtube.com/channel/UCllE-GsYy10RkxBUK0HIffw) which has video tutorials on different topics
- You can also learn from [codelabs](https://leafphp.dev/codelabs/) and contribute as well.

## 😇 Contributing

We are glad to have you. All contributions are welcome! To get started, familiarize yourself with our [contribution guide](https://leafphp.dev/community/contributing.html) and you'll be ready to make your first pull request 🚀.

To report a security vulnerability, you can reach out to [@mychidarko](https://twitter.com/mychidarko) or [@leafphp](https://twitter.com/leafphp) on twitter. We will coordinate the fix and eventually commit the solution in this project.

## 🤩 Sponsoring Leaf

We are committed to keeping Leaf open-source and free, but maintaining and developing new features now requires significant time and resources. As the project has grown, so have the costs, which have been mostly covered by the team. To sustain and grow Leaf, we need your help to support full-time maintainers.

You can sponsor Leaf and any of our packages on [open collective](https://opencollective.com/leaf) or check the [contribution page](https://leafphp.dev/support/) for a list of ways to contribute.

And to all our [existing cash/code contributors](https://leafphp.dev#sponsors), we love you all ❤️
