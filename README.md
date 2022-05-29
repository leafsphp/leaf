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

Leaf 3 brings a much cleaner, faster and simpler workflow to your apps. Powered by an ecosystem of powerful modules with zero setup and it's ease of use, Leaf now allows you to tackle complexities no matter the scale.

## 📦 Installation

**Install Leaf 3:**

You can create a Leaf 3 app with the [Leaf CLI](https://cli.leafphp.dev)

```sh
leaf create <project-name> --v3 --basic
```

`<project-name>` is your project name

You can also use [Composer](https://getcomposer.org/) to install Leaf 3 in your project quickly.

```bash
composer require leafs/leaf
```

**Install Leaf 2:**

Since leaf 3 is still in beta, you might want to install the stable leaf 2 instead. You can do this with Leaf CLI or composer.

```sh
leaf create <project-name> --basic --v2
```

Or with composer

```sh
composer require leafs/leaf v2.6
```

## 🗂 Basic Usage

This is a simple demonstration of Leaf's simplicity.
After [installing](#installation) Leaf, create an _index.php_ file.

```php
<?php

require __DIR__ . "/vendor/autoload.php";

app()->get("/", function () {
  response()->json([
    "message" => "Welcome!"
  ]);
});

app()->run();
```

**If you use the Leaf CLI, this is already done for you 🚀.**

You may quickly test this using the built-in PHP server:

```sh
php -S localhost:8000
```

Or with the Leaf CLI:

```sh
leaf serve
```

**You can view the full documentation [here](https://leafphp.dev/)**

## ✈️ The Leaf Ecosystem (Libs & Frameworks)

| Project    | Status                                                                                                                                                                                                                                         | Description                                            |
| ---------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | ------------------------------------------------------ |
| [leaf](https://github.com/leafsphp/leaf)     | [![Latest Stable Version](https://poser.pugx.org/leafs/leaf/v/stable)](https://packagist.org/packages/leafs/leaf) [![Total Downloads](https://poser.pugx.org/leafs/leaf/downloads)](https://packagist.org/packages/leafs/leaf)                 | Create websites and APIs quickly                       |
| [leafmvc](https://github.com/leafsphp/leafmvc)  | [![Latest Stable Version](https://poser.pugx.org/leafs/mvc/v/stable)](https://packagist.org/packages/leafs/mvc) [![Total Downloads](https://poser.pugx.org/leafs/mvc/downloads)](https://packagist.org/packages/leafs/mvc)                     | An MVC wrapper for leaf (for general development)      |
| [leafapi](https://github.com/leafsphp/leafapi)  | [![Latest Stable Version](https://poser.pugx.org/leafs/api/v/stable)](https://packagist.org/packages/leafs/api) [![Total Downloads](https://poser.pugx.org/leafs/api/downloads)](https://packagist.org/packages/leafs/api)                     | An MVC wrapper for leaf geared towards API development |
| [skeleton](https://github.com/leafsphp/skeleton) | [![Latest Stable Version](https://poser.pugx.org/leafs/skeleton/v/stable)](https://packagist.org/packages/leafs/skeleton) [![Total Downloads](https://poser.pugx.org/leafs/skeleton/downloads)](https://packagist.org/packages/leafs/skeleton) | Leaf boilerplate for rapid development                 |
| [leaf-ui](https://github.com/leafsphp/leaf-ui)  | [![Latest Stable Version](https://poser.pugx.org/leafs/ui/v/stable)](https://packagist.org/packages/leafs/ui) [![Total Downloads](https://poser.pugx.org/leafs/ui/downloads)](https://packagist.org/packages/leafs/ui)                         | A PHP library for building user interfaces             |
| [cli](https://github.com/leafsphp/cli)      | [![Latest Stable Version](https://poser.pugx.org/leafs/cli/v/stable)](https://packagist.org/packages/leafs/cli) [![Total Downloads](https://poser.pugx.org/leafs/cli/downloads)](https://packagist.org/packages/leafs/cli)                     | CLI for creating & interacting with your leaf apps                |

## 🧩 The Leaf Ecosystem (Modules)

| Project                | Status                                                                                                                                                                                                                                                         | Description                                                       |
| ---------------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | ----------------------------------------------------------------- |
| [aloe](https://github.com/leafsphp/aloe)     | [![Latest Stable Version](https://poser.pugx.org/leafs/aloe/v/stable)](https://packagist.org/packages/leafs/aloe) [![Total Downloads](https://poser.pugx.org/leafs/aloe/downloads)](https://packagist.org/packages/leafs/aloe) | Smart console helper for leaf mvc, leaf api and skeleton |
| [router](https://github.com/leafsphp/router)     | [![Latest Stable Version](https://poser.pugx.org/leafs/router/v/stable)](https://packagist.org/packages/leafs/router) [![Total Downloads](https://poser.pugx.org/leafs/router/downloads)](https://packagist.org/packages/leafs/router) | Default router for leaf php                                |
| [experiments](https://github.com/leafsphp/experimental-modules) | [![Latest Stable Version](https://poser.pugx.org/leafs/experimental/v/stable)](https://packagist.org/packages/leafs/experimental) [![Total Downloads](https://poser.pugx.org/leafs/experimental/downloads)](https://packagist.org/packages/leafs/experimental) | collection of experimental modules                                |
| [mail](https://github.com/leafsphp/mail)                 | [![Latest Stable Version](https://poser.pugx.org/leafs/mail/v/stable)](https://packagist.org/packages/leafs/mail) [![Total Downloads](https://poser.pugx.org/leafs/mail/downloads)](https://packagist.org/packages/leafs/mail)                                 | Mailing made easy with leaf                                       |
| [auth](https://github.com/leafsphp/auth)                 | [![Latest Stable Version](https://poser.pugx.org/leafs/auth/v/stable)](https://packagist.org/packages/leafs/auth) [![Total Downloads](https://poser.pugx.org/leafs/auth/downloads)](https://packagist.org/packages/leafs/auth)                                 | Simple but powerful authentication system for your apps           |
| [form](https://github.com/leafsphp/form)                 | [![Latest Stable Version](https://poser.pugx.org/leafs/form/v/stable)](https://packagist.org/packages/leafs/form) [![Total Downloads](https://poser.pugx.org/leafs/form/downloads)](https://packagist.org/packages/leafs/form)                                 | Form processes and validation                                     |
| [password](https://github.com/leafsphp/password)             | [![Latest Stable Version](https://poser.pugx.org/leafs/password/v/stable)](https://packagist.org/packages/leafs/password) [![Total Downloads](https://poser.pugx.org/leafs/password/downloads)](https://packagist.org/packages/leafs/password)                 | Password encryption/validation/hashing in one box                 |
| [db-old](https://github.com/leafsphp/db-old)               | [![Latest Stable Version](https://poser.pugx.org/leafs/db-old/v/stable)](https://packagist.org/packages/leafs/db-old) [![Total Downloads](https://poser.pugx.org/leafs/db-old/downloads)](https://packagist.org/packages/leafs/db-old)                         | Leaf Db from v1 (still maintained)                                |
| [db](https://github.com/leafsphp/db)                   | [![Latest Stable Version](https://poser.pugx.org/leafs/db/v/stable)](https://packagist.org/packages/leafs/db) [![Total Downloads](https://poser.pugx.org/leafs/db/downloads)](https://packagist.org/packages/leafs/db)                                         | Leaf Db from v2 (actively maintained)                             |
| [session](https://github.com/leafsphp/session)              | [![Latest Stable Version](https://poser.pugx.org/leafs/session/v/stable)](https://packagist.org/packages/leafs/session) [![Total Downloads](https://poser.pugx.org/leafs/session/downloads)](https://packagist.org/packages/leafs/session)                     | PHP sessions made simple                                          |
| [cookie](https://github.com/leafsphp/cookie)               | [![Latest Stable Version](https://poser.pugx.org/leafs/cookie/v/stable)](https://packagist.org/packages/leafs/cookie) [![Total Downloads](https://poser.pugx.org/leafs/cookie/downloads)](https://packagist.org/packages/leafs/cookie)                         | Cookie management without the tears                               |
| [http](https://github.com/leafsphp/http)                 | [![Latest Stable Version](https://poser.pugx.org/leafs/http/v/stable)](https://packagist.org/packages/leafs/http) [![Total Downloads](https://poser.pugx.org/leafs/http/downloads)](https://packagist.org/packages/leafs/http)                                 | Http operations made simple (request, response, ...)              |
| [cors](https://github.com/leafsphp/cors)                 | [![Latest Stable Version](https://poser.pugx.org/leafs/cors/v/stable)](https://packagist.org/packages/leafs/cors) [![Total Downloads](https://poser.pugx.org/leafs/cors/downloads)](https://packagist.org/packages/leafs/cors)                                 | CORS operations made simple          |
| [csrf](https://github.com/leafsphp/csrf)               | [![Latest Stable Version](https://poser.pugx.org/leafs/csrf/v/stable)](https://packagist.org/packages/leafs/csrf) [![Total Downloads](https://poser.pugx.org/leafs/csrf/downloads)](https://packagist.org/packages/leafs/csrf)                         | Basic CSRF protection                                              |
| [anchor](https://github.com/leafsphp/anchor)               | [![Latest Stable Version](https://poser.pugx.org/leafs/anchor/v/stable)](https://packagist.org/packages/leafs/anchor) [![Total Downloads](https://poser.pugx.org/leafs/anchor/downloads)](https://packagist.org/packages/leafs/anchor)                         | Basic security tools                                              |
| [logger](https://github.com/leafsphp/logger)                   | [![Latest Stable Version](https://poser.pugx.org/leafs/logger/v/stable)](https://packagist.org/packages/leafs/logger) [![Total Downloads](https://poser.pugx.org/leafs/logger/downloads)](https://packagist.org/packages/leafs/logger)                                         | leaf logger module                     |
| [fs](https://github.com/leafsphp/fs)                   | [![Latest Stable Version](https://poser.pugx.org/leafs/fs/v/stable)](https://packagist.org/packages/leafs/fs) [![Total Downloads](https://poser.pugx.org/leafs/fs/downloads)](https://packagist.org/packages/leafs/fs)                                         | Awesome filesystem operations + file uploads                      |
| [date](https://github.com/leafsphp/date)                 | [![Latest Stable Version](https://poser.pugx.org/leafs/date/v/stable)](https://packagist.org/packages/leafs/date) [![Total Downloads](https://poser.pugx.org/leafs/date/downloads)](https://packagist.org/packages/leafs/date)                                 | PHP dates for humans                                              |
| [bareui](https://github.com/leafsphp/bareui)               | [![Latest Stable Version](https://poser.pugx.org/leafs/bareui/v/stable)](https://packagist.org/packages/leafs/bareui) [![Total Downloads](https://poser.pugx.org/leafs/bareui/downloads)](https://packagist.org/packages/leafs/bareui)                         | Dead simple templating engine with no compilation (blazing speed) |
| [blade](https://github.com/leafsphp/blade)                | [![Latest Stable Version](https://poser.pugx.org/leafs/blade/v/stable)](https://packagist.org/packages/leafs/blade) [![Total Downloads](https://poser.pugx.org/leafs/blade/downloads)](https://packagist.org/packages/leafs/blade)                             | Laravel blade templating port for leaf                            |
| [veins](https://github.com/leafsphp/veins)                | [![Latest Stable Version](https://poser.pugx.org/leafs/veins/v/stable)](https://packagist.org/packages/leafs/veins) [![Total Downloads](https://poser.pugx.org/leafs/veins/downloads)](https://packagist.org/packages/leafs/veins)                             | Leaf veins templating engine                                      |
| [mvc-core](https://github.com/leafsphp/mvc-core)             | [![Latest Stable Version](https://poser.pugx.org/leafs/mvc-core/v/stable)](https://packagist.org/packages/leafs/mvc-core) [![Total Downloads](https://poser.pugx.org/leafs/mvc-core/downloads)](https://packagist.org/packages/leafs/mvc-core)                 | Core MVC tools powering our MVC wrappers                          |
| [exception](https://github.com/leafsphp/exceptions)                 | [![Latest Stable Version](https://poser.pugx.org/leafs/exception/v/stable)](https://packagist.org/packages/leafs/exception) [![Total Downloads](https://poser.pugx.org/leafs/exception/downloads)](https://packagist.org/packages/leafs/exception)    | Leaf's exception wrapper (fork of whoops)                           |
| [fetch](https://github.com/leafsphp/fetch)                | [![Latest Stable Version](https://poser.pugx.org/leafs/fetch/v/stable)](https://packagist.org/packages/leafs/fetch) [![Total Downloads](https://poser.pugx.org/leafs/fetch/downloads)](https://packagist.org/packages/leafs/fetch)                             | HTTP requests made simple                                         |
| [redis](https://github.com/leafsphp/redis)                | [![Latest Stable Version](https://poser.pugx.org/leafs/redis/v/stable)](https://packagist.org/packages/leafs/redis) [![Total Downloads](https://poser.pugx.org/leafs/redis/downloads)](https://packagist.org/packages/leafs/redis)                             | Redis module                                                      |

## 💬 Stay In Touch

- [Twitter](https://twitter.com/leafphp)
- [Join the forum](https://github.com/leafsphp/leaf/discussions/37)
- [Chat on discord](https://discord.com/invite/Pkrm9NJPE3)

## 📓 Learning Leaf 3

- Leaf has a very easy to understand [documentation](https://leafphp.dev) which contains information on all operations in Leaf.
- You can also check out our [youtube channel](https://www.youtube.com/channel/UCllE-GsYy10RkxBUK0HIffw) which has video tutorials on different topics
- You can also learn from [codelabs](https://codelabs.leafphp.dev) and contribute as well.

## 😇 Contributing

We are glad to have you. All contributions are welcome! To get started, familiarize yourself with our [contribution guide](https://leafphp.dev/community/contributing.html) and you'll be ready to make your first pull request 🚀.

To report a security vulnerability, you can reach out to [@mychidarko](https://twitter.com/mychidarko) or [@leafphp](https://twitter.com/leafphp) on twitter. We will coordinate the fix and eventually commit the solution in this project.

### Code contributors

<table>
	<tr>
		<td align="center">
			<a href="https://github.com/mychidarko">
				<img src="https://avatars.githubusercontent.com/u/26604242?v=4" width="100%" alt=""/>
				<br />
				<sub>
					<b>Michael Darko</b>
				</sub>
			</a>
		</td>
		<td align="center">
			<a href="https://github.com/ftonato">
				<img src="https://avatars.githubusercontent.com/u/5417662?v=4" width="100%" alt=""/>
				<br />
				<sub><b>Ademílson F. Tonato</b></sub>
			</a>
		</td>
		<td align="center">
			<a href="https://github.com/herber">
				<img src="https://avatars.githubusercontent.com/u/22559657?&v=4" width="100%" alt=""/>
				<br />
				<sub><b>Tobias Herber</b></sub>
			</a>
		</td>
		<td align="center">
			<a href="https://github.com/pjotrsavitski">
				<img src="https://avatars.githubusercontent.com/u/518331?&v=4" width="100%" alt=""/>
				<br />
				<sub><b>Pjotr Savitski</b></sub>
			</a>
		</td>
	</tr>
	<tr>
		<td align="center">
			<a href="https://github.com/pablouser1">
				<img src="https://avatars.githubusercontent.com/u/17802865?&v=4" width="100%" alt=""/>
				<br />
				<sub><b>Pablo Ferreiro</b></sub>
			</a>
		</td>
		<td align="center">
			<a href="https://github.com/monkeywithacupcake">
				<img src="https://avatars.githubusercontent.com/u/7316730?v=4" width="100%" alt=""/>
				<br />
				<sub><b>jess</b></sub>
			</a>
		</td>
		<td align="center">
			<a href="https://github.com/Awilum">
				<img src="https://avatars.githubusercontent.com/u/477114?v=4" width="100%" alt=""/>
				<br />
				<sub><b>Sergey Romanenko</b></sub>
			</a>
		</td>
	</tr>
</table>

## 🤩 Sponsoring Leaf

Your cash contributions go a long way to help us make Leaf even better for you. You can sponsor Leaf and any of our packages on [open collective](https://opencollective.com/leaf) or check the [contribution page](https://leafphp.dev/support/) for a list of ways to contribute.

And to all our existing cash/code contributors, we love you all ❤️

### Cash contributors

<table>
	<tr>
		<td align="center">
			<a href="https://opencollective.com/aaron-smith3">
				<img src="https://images.opencollective.com/aaron-smith3/08ee620/avatar/256.png" width="120px" alt=""/>
				<br />
				<sub><b>Aaron Smith</b></sub>
			</a>
		</td>
		<td align="center">
			<a href="https://opencollective.com/peter-bogner">
				<img src="https://images.opencollective.com/peter-bogner/avatar/256.png" width="120px" alt=""/>
				<br />
				<sub><b>Peter Bogner</b></sub>
			</a>
		</td>
		<td align="center">
			<a href="#">
				<img src="https://images.opencollective.com/guest-32634fda/avatar.png" width="120px" alt=""/>
				<br />
				<sub><b>Vano</b></sub>
			</a>
		</td>
		<td align="center">
          <a href="#">
            <img
              src="https://images.opencollective.com/guest-c72a498e/avatar.png"
              width="120px"
              alt=""
            />
            <br />
            <sub><b>Casprine</b></sub>
          </a>
        </td>
	</tr>
</table>

## 🤯 Links/Projects

- [Leaf Docs](https://leafphp.dev)
- [Leaf MVC](https://mvc.leafphp.dev)
- [Leaf API](https://api.leafphp.dev)
- [Leaf CLI](https://cli.leafphp.dev)
- [Aloe CLI](https://leafphp.dev/aloe-cli/)
