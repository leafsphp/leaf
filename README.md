# Leaf PHP Quick Start v1

This is a simple boilerplate good for any PHP project without a framework. It's simple and contains a ton of functionality all written in plain PHP.

## Project Set up

```bash
$ git clone https://github.com/mr-phlames/leaf-php-boilerplate.git
$ cd vier-api
$ php -S localhost:8000
```

or launch serve.bat

This will start a server on Port 8000. Open up the code and start editing.

## Project Structure
`
+-- index.php
+-- .htaccess
+-- .docs
|   +-- index.php
+-- routes
|   +-- index.php
+-- src
|   +-- config
|    |	  +-- db.php
|    |	  +-- headers.php
|    |	  +-- init.php
|   +-- core
|    |	  +-- date.php
|    |	  +-- fieldValidate.php
|    |	  +-- request.php
|    |	  +-- respond.php
|   +-- helpers
|    |	  +-- constants.php
|    |	  +-- jwt.php 
|   +-- router
|    |	  +-- Irequest.php
|    |	  +-- Request.php
|    |	  +-- Router.php
`

##### NB:
`index.php` is the entry point of the project. All requests are redirected to the `index.php`. This is achieved through the `.htaccess` file. From there, the appRouter picks up all requests made to the app. 

`init.php` is where all the core and helper classes are "registered".

## Routing

The `/routes` folder contains the route files of the API. By default, the `routes` folder contains an index.php file which is included in `index.php`.
##### NB: Only GET and POST requests are supported currently

The app router can be found in `/src/router/Router.php` and is registered in 

### Simple Routing
Get Requests
```php
<?php

$router->get('/home', function() use($response) {
	return $response->respond(/*data*/);
});
```

View the [Leaf starter documentation](https://github.com/mr-phlames/leaf-php-boilerplate) for more on routing.



## Database connection

In the _src/config/db.php_, connection variables are declared at the top of the file, enter your own details for your database.

```php
<?php

class Database {
	private $host = 'localhost';
	private $user = 'root';
	private $password = '';
	private $dbname = 'books';
	// these were added to allow easy switching between local dev environment and the hosting platform 
	// private $user = 'id11174187_root';
	// private $password = '***********';
	// private $dbname = 'id11174187_vierdb';
```

# The _docs_ for this project are incomplete, use the readme instead