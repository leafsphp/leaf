<!-- markdownlint-disable no-inline-html -->
<p align="center">
  <br><br>
  <img src="https://leafphp.dev/logo-circle.png" height="100"/>
  <br>
</p>

<h1 align="center">Leaf 3</h1>

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

Leaf is a PHP framework that helps you create clean, simple but powerful web apps and APIs quickly and easily. Leaf introduces a cleaner and much simpler structure to the PHP language while maintaining it's flexibility. With a simple structure and a shallow learning curve, it's an excellent way to rapidly build powerful and high performant web apps and APIs.

## 🗂 Basic Usage

This is a "hello world" application created using Leaf. After [installing](#-installation) Leaf, create an _index.php_ file.

```php
<?php

require __DIR__ . '/vendor/autoload.php';

app()->get('/', function () {
  response()->json([
    'message' => 'Welcome!'
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
php -S localhost:8000
```

## 🍁 Why Leaf?

When it comes to building web applications, there are numerous tools and frameworks at your disposal. Nevertheless, we are convinced that Leaf is the optimal selection for developing powerful, web applications and APIs.

### The problems

While PHP frameworks can make web development faster and more efficient, there are some potential challenges or drawbacks to using them, including:

- Learning curve: Most PHP frameworks have a steep learning curve, especially for developers who are new to the language or the framework's conventions.
- Performance overhead: Some PHP frameworks can add unnecessary performance overhead, due to the additional abstraction layers and features they provide.
- Code maintenance: Most frameworks require adhering to specific coding standards and conventions, which can make maintenance and updates more challenging if you are not already familiar with those standards.
- Limited flexibility: PHP frameworks can be more rigid than writing code from scratch, as they may require you to adhere to specific coding standards and conventions. This can limit your flexibility in terms of how you structure your code and handle specific use cases.
- Compatibility with other systems: Most PHP frameworks are bound to a particular ecosystem and make it difficult to randomly pick and use packages which don't have support for the framework you are using.
- Packing a ton of unused code/packages: Just about every PHP framework out there adds a ton of complexity to your applications in the form of unused code, classes and packages. This in turn leads to bloat and ultimately a drop in performance

### How Leaf tackles these

Leaf 3 provides a bunch of features that aim to tackle these common problems found in just about every PHP framework out there.

- #### Low barrier to entry

    Leaf is the easiest framework to learn with PHP newbies building powerful leaf apps in a few minutes of reading the docs/watching out tutorial videos. All you truly need to get started with Leaf is basic PHP knowledge and optional but recommended knowledge on some backend concepts like JWT auth and more.

- #### Lightweight

    Leaf 2 was lightweight and fast enough to be considered one of the most lightweight but powerful frameworks around, and Leaf 3 makes leaf 2 look like a joke. Leaf 3 can now be considered the most lightweight PHP framework with a source of about 30kb and allows you to build full apps and APIs which end up less than 20mb including user dependencies (leaf api). This is a big haul compared to other frameworks that require dependencies and tons of files which end up more than 200mb.

    ![image](https://user-images.githubusercontent.com/26604242/146754044-4c71c4ec-7b37-4c85-9c8b-56e8c2b54831.png)

    > a comparison with slim - slim (left) - leaf (right)

- #### Enables high developer productivity

    A whole lot of research and testing has been done to build amazing features which allow developers to focus on only what they need: their apps. Leaf 3 has put tons of strategies together to create the best developer experience known to PHP. From things like removing class initializers and creating global functions which allow you call classes from anywhere in your application, modules and other amazing leaf features.

- #### Powered by [modules](https://leafphp.dev/modules/)

    Leaf 3 and its ecosystem are heavily powered by modules, which are simply pieces of leaf's functionality shipped into independently installable libraries. Modules help make leaf even more lightweight and help developers only deal with features which they need in their applications. This means that you only install what you need.

- #### Easy to use features

    As mentioned above, a lot of research has gone into the developer experience for leaf 3 and one aspect was to make our existing features more performant and easier to use. We employed various strategies like modeling some features after popular libraries in other languages and frameworks. For instance, the API for leaf cors is almost an exact replica of the expressjs cors middleware.

- #### Library/Framework compatibility

    Since the beginning of Leaf, we've set out to create code which could easily be integrated with other libraries and frameworks. No matter how powerful leaf is, we try to base of everything we do on simple concepts as opposed to other frameworks which need things like providers in order to access framework features in libraries.

- #### Scalability

    One of the most beautiful things about leaf is that, no matter what package you're using with leaf, if it works in development, it will definitely work in production with near zero config, unless you want some special features. Leaf provides a core and other frameworks/libraries that build around leaf. This makes leaf appropriate for almost any project no matter it's size.

## 📦 Installation

You can create a Leaf 3 app with the [Leaf CLI](https://cli.leafphp.dev)

```bash
leaf create <project-name> --v3 --basic
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
| [leafmvc](https://github.com/leafsphp/leafmvc)   | [![Latest Stable Version](https://poser.pugx.org/leafs/mvc/v/stable)](https://packagist.org/packages/leafs/mvc) [![Total Downloads](https://poser.pugx.org/leafs/mvc/downloads)](https://packagist.org/packages/leafs/mvc)                     | An MVC wrapper for leaf (for general development)      |
| [leafapi](https://github.com/leafsphp/leafapi)   | [![Latest Stable Version](https://poser.pugx.org/leafs/api/v/stable)](https://packagist.org/packages/leafs/api) [![Total Downloads](https://poser.pugx.org/leafs/api/downloads)](https://packagist.org/packages/leafs/api)                     | An MVC wrapper for leaf geared towards API development |
| [skeleton](https://github.com/leafsphp/skeleton) | [![Latest Stable Version](https://poser.pugx.org/leafs/skeleton/v/stable)](https://packagist.org/packages/leafs/skeleton) [![Total Downloads](https://poser.pugx.org/leafs/skeleton/downloads)](https://packagist.org/packages/leafs/skeleton) | Leaf boilerplate for rapid development                 |
| [leaf-ui](https://github.com/leafsphp/leaf-ui)   | [![Latest Stable Version](https://poser.pugx.org/leafs/ui/v/stable)](https://packagist.org/packages/leafs/ui) [![Total Downloads](https://poser.pugx.org/leafs/ui/downloads)](https://packagist.org/packages/leafs/ui)                         | A PHP library for building user interfaces             |
| [cli](https://github.com/leafsphp/cli)           | [![Latest Stable Version](https://poser.pugx.org/leafs/cli/v/stable)](https://packagist.org/packages/leafs/cli) [![Total Downloads](https://poser.pugx.org/leafs/cli/downloads)](https://packagist.org/packages/leafs/cli)                     | CLI for creating & interacting with your leaf apps     |

## 🧩 The Leaf Ecosystem (Modules)

| Project                                                         | Status                                                                                                                                                                                                                                                         | Description                                                       |
| --------------------------------------------------------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | ----------------------------------------------------------------- |
| [alchemy](https://github.com/leafsphp/alchemy)                  | [![Latest Stable Version](https://poser.pugx.org/leafs/alchemy/v/stable)](https://packagist.org/packages/leafs/alchemy) [![Total Downloads](https://poser.pugx.org/leafs/alchemy/downloads)](https://packagist.org/packages/leafs/alchemy)                     | A test runner for your Leaf apps                                   |
| [aloe](https://github.com/leafsphp/aloe)                        | [![Latest Stable Version](https://poser.pugx.org/leafs/aloe/v/stable)](https://packagist.org/packages/leafs/aloe) [![Total Downloads](https://poser.pugx.org/leafs/aloe/downloads)](https://packagist.org/packages/leafs/aloe)                                 | Smart console helper for leaf mvc, leaf api and skeleton          |
| [anchor](https://github.com/leafsphp/anchor)                    | [![Latest Stable Version](https://poser.pugx.org/leafs/anchor/v/stable)](https://packagist.org/packages/leafs/anchor) [![Total Downloads](https://poser.pugx.org/leafs/anchor/downloads)](https://packagist.org/packages/leafs/anchor)                         | Basic security tools                                              |
| [auth](https://github.com/leafsphp/auth)                        | [![Latest Stable Version](https://poser.pugx.org/leafs/auth/v/stable)](https://packagist.org/packages/leafs/auth) [![Total Downloads](https://poser.pugx.org/leafs/auth/downloads)](https://packagist.org/packages/leafs/auth)                                 | Simple but powerful authentication system for your apps           |
| [bareui](https://github.com/leafsphp/bareui)                    | [![Latest Stable Version](https://poser.pugx.org/leafs/bareui/v/stable)](https://packagist.org/packages/leafs/bareui) [![Total Downloads](https://poser.pugx.org/leafs/bareui/downloads)](https://packagist.org/packages/leafs/bareui)                         | Dead simple templating engine with no compilation (blazing speed) |
| [blade](https://github.com/leafsphp/blade)                      | [![Latest Stable Version](https://poser.pugx.org/leafs/blade/v/stable)](https://packagist.org/packages/leafs/blade) [![Total Downloads](https://poser.pugx.org/leafs/blade/downloads)](https://packagist.org/packages/leafs/blade)                             | Laravel blade templating port for leaf                            |
| [cookie](https://github.com/leafsphp/cookie)                    | [![Latest Stable Version](https://poser.pugx.org/leafs/cookie/v/stable)](https://packagist.org/packages/leafs/cookie) [![Total Downloads](https://poser.pugx.org/leafs/cookie/downloads)](https://packagist.org/packages/leafs/cookie)                         | Cookie management without the tears                               |
| [cors](https://github.com/leafsphp/cors)                        | [![Latest Stable Version](https://poser.pugx.org/leafs/cors/v/stable)](https://packagist.org/packages/leafs/cors) [![Total Downloads](https://poser.pugx.org/leafs/cors/downloads)](https://packagist.org/packages/leafs/cors)                                 | CORS operations made simple                                       |
| [csrf](https://github.com/leafsphp/csrf)                        | [![Latest Stable Version](https://poser.pugx.org/leafs/csrf/v/stable)](https://packagist.org/packages/leafs/csrf) [![Total Downloads](https://poser.pugx.org/leafs/csrf/downloads)](https://packagist.org/packages/leafs/csrf)                                 | Basic CSRF protection                                             |
| [date](https://github.com/leafsphp/date)                        | [![Latest Stable Version](https://poser.pugx.org/leafs/date/v/stable)](https://packagist.org/packages/leafs/date) [![Total Downloads](https://poser.pugx.org/leafs/date/downloads)](https://packagist.org/packages/leafs/date)                                 | PHP dates for humans                                              |
| [db](https://github.com/leafsphp/db)                            | [![Latest Stable Version](https://poser.pugx.org/leafs/db/v/stable)](https://packagist.org/packages/leafs/db) [![Total Downloads](https://poser.pugx.org/leafs/db/downloads)](https://packagist.org/packages/leafs/db)                                         | Leaf Db from v2 (actively maintained)                             |
| [db-old](https://github.com/leafsphp/db-old)                    | [![Latest Stable Version](https://poser.pugx.org/leafs/db-old/v/stable)](https://packagist.org/packages/leafs/db-old) [![Total Downloads](https://poser.pugx.org/leafs/db-old/downloads)](https://packagist.org/packages/leafs/db-old)                         | Leaf Db from v1 (still maintained)                                |
| [exception](https://github.com/leafsphp/exceptions)             | [![Latest Stable Version](https://poser.pugx.org/leafs/exception/v/stable)](https://packagist.org/packages/leafs/exception) [![Total Downloads](https://poser.pugx.org/leafs/exception/downloads)](https://packagist.org/packages/leafs/exception)             | Leaf's exception wrapper (fork of whoops)                         |
| [experiments](https://github.com/leafsphp/experimental-modules) | [![Latest Stable Version](https://poser.pugx.org/leafs/experimental/v/stable)](https://packagist.org/packages/leafs/experimental) [![Total Downloads](https://poser.pugx.org/leafs/experimental/downloads)](https://packagist.org/packages/leafs/experimental) | collection of experimental modules                                |
| [fetch](https://github.com/leafsphp/fetch)                      | [![Latest Stable Version](https://poser.pugx.org/leafs/fetch/v/stable)](https://packagist.org/packages/leafs/fetch) [![Total Downloads](https://poser.pugx.org/leafs/fetch/downloads)](https://packagist.org/packages/leafs/fetch)                             | HTTP requests made simple                                         |
| [form](https://github.com/leafsphp/form)                        | [![Latest Stable Version](https://poser.pugx.org/leafs/form/v/stable)](https://packagist.org/packages/leafs/form) [![Total Downloads](https://poser.pugx.org/leafs/form/downloads)](https://packagist.org/packages/leafs/form)                                 | Form processes and validation                                     |
| [fs](https://github.com/leafsphp/fs)                            | [![Latest Stable Version](https://poser.pugx.org/leafs/fs/v/stable)](https://packagist.org/packages/leafs/fs) [![Total Downloads](https://poser.pugx.org/leafs/fs/downloads)](https://packagist.org/packages/leafs/fs)                                         | Awesome filesystem operations + file uploads                      |
| [http](https://github.com/leafsphp/http)                        | [![Latest Stable Version](https://poser.pugx.org/leafs/http/v/stable)](https://packagist.org/packages/leafs/http) [![Total Downloads](https://poser.pugx.org/leafs/http/downloads)](https://packagist.org/packages/leafs/http)                                 | Http operations made simple (request, response, ...)              |
| [logger](https://github.com/leafsphp/logger)                    | [![Latest Stable Version](https://poser.pugx.org/leafs/logger/v/stable)](https://packagist.org/packages/leafs/logger) [![Total Downloads](https://poser.pugx.org/leafs/logger/downloads)](https://packagist.org/packages/leafs/logger)                         | leaf logger module                                                |
| [mail](https://github.com/leafsphp/mail)                        | [![Latest Stable Version](https://poser.pugx.org/leafs/mail/v/stable)](https://packagist.org/packages/leafs/mail) [![Total Downloads](https://poser.pugx.org/leafs/mail/downloads)](https://packagist.org/packages/leafs/mail)                                 | Mailing made easy with leaf                                       |
| [mvc-core](https://github.com/leafsphp/mvc-core)                | [![Latest Stable Version](https://poser.pugx.org/leafs/mvc-core/v/stable)](https://packagist.org/packages/leafs/mvc-core) [![Total Downloads](https://poser.pugx.org/leafs/mvc-core/downloads)](https://packagist.org/packages/leafs/mvc-core)                 | Core MVC tools powering our MVC wrappers                          |
| [password](https://github.com/leafsphp/password)                | [![Latest Stable Version](https://poser.pugx.org/leafs/password/v/stable)](https://packagist.org/packages/leafs/password) [![Total Downloads](https://poser.pugx.org/leafs/password/downloads)](https://packagist.org/packages/leafs/password)                 | Password encryption/validation/hashing in one box                 |
| [redis](https://github.com/leafsphp/redis)                      | [![Latest Stable Version](https://poser.pugx.org/leafs/redis/v/stable)](https://packagist.org/packages/leafs/redis) [![Total Downloads](https://poser.pugx.org/leafs/redis/downloads)](https://packagist.org/packages/leafs/redis)                             | Redis module                                                      |
| [router](https://github.com/leafsphp/router)                    | [![Latest Stable Version](https://poser.pugx.org/leafs/router/v/stable)](https://packagist.org/packages/leafs/router) [![Total Downloads](https://poser.pugx.org/leafs/router/downloads)](https://packagist.org/packages/leafs/router)                         | Default router for leaf php                                       |
| [session](https://github.com/leafsphp/session)                  | [![Latest Stable Version](https://poser.pugx.org/leafs/session/v/stable)](https://packagist.org/packages/leafs/session) [![Total Downloads](https://poser.pugx.org/leafs/session/downloads)](https://packagist.org/packages/leafs/session)                     | PHP sessions made simple                                          |
| [tilly](https://github.com/leafsphp/tilly)                      | [![Latest Stable Version](https://poser.pugx.org/leafs/tilly/v/stable)](https://packagist.org/packages/leafs/tilly) [![Total Downloads](https://poser.pugx.org/leafs/tilly/downloads)](https://packagist.org/packages/leafs/tilly)                             | Simple utility 'toolkit' for PHP applications                     |
| [veins](https://github.com/leafsphp/veins)                      | [![Latest Stable Version](https://poser.pugx.org/leafs/veins/v/stable)](https://packagist.org/packages/leafs/veins) [![Total Downloads](https://poser.pugx.org/leafs/veins/downloads)](https://packagist.org/packages/leafs/veins)                             | Leaf veins templating engine                                      |
| [viewi](https://github.com/leafsphp/viewi)                      | [![Latest Stable Version](https://poser.pugx.org/leafs/viewi/v/stable)](https://packagist.org/packages/leafs/viewi) [![Total Downloads](https://poser.pugx.org/leafs/viewi/downloads)](https://packagist.org/packages/leafs/viewi)                             | Leaf integration with Viewi PHP                                   |

## 💬 Stay In Touch

-   [Twitter](https://twitter.com/leafphp)
-   [Join the forum](https://github.com/leafsphp/leaf/discussions/37)
-   [Chat on discord](https://discord.com/invite/Pkrm9NJPE3)

## 📓 Learning Leaf 3

-   Leaf has a very easy to understand [documentation](https://leafphp.dev) which contains information on all operations in Leaf.
-   You can also check out our [youtube channel](https://www.youtube.com/channel/UCllE-GsYy10RkxBUK0HIffw) which has video tutorials on different topics
-   You can also learn from [codelabs](https://codelabs.leafphp.dev) and contribute as well.

## 😇 Contributing

We are glad to have you. All contributions are welcome! To get started, familiarize yourself with our [contribution guide](https://leafphp.dev/community/contributing.html) and you'll be ready to make your first pull request 🚀.

To report a security vulnerability, you can reach out to [@mychidarko](https://twitter.com/mychidarko) or [@leafphp](https://twitter.com/leafphp) on twitter. We will coordinate the fix and eventually commit the solution in this project.

## 🤩 Sponsoring Leaf

Your cash contributions go a long way to help us make Leaf even better for you. You can sponsor Leaf and any of our packages on [open collective](https://opencollective.com/leaf) or check the [contribution page](https://leafphp.dev/support/) for a list of ways to contribute.

And to all our [existing cash/code contributors](https://leafphp.dev#sponsors), we love you all ❤️

## 🤯 Links/Projects

-   [Leaf Docs](https://leafphp.dev)
-   [Leaf MVC](https://mvc.leafphp.dev)
-   [Leaf API](https://api.leafphp.dev)
-   [Leaf CLI](https://cli.leafphp.dev)
-   [Aloe CLI](https://leafphp.dev/aloe-cli/)
