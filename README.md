# Leaf PHP Quick Start v1

This is a simple boilerplate good for any PHP project without a framework. It's simple and contains a ton of functionality all written in plain PHP.

## Project Set up

```bash
$ git clone https://github.com/mr-phlames/leaf-php-boilerplate.git
$ cd leaf-php-boilerplate
$ php -S localhost:8000
```

or launch serve.bat

This will start a server on Port 8000. Open up the code and start editing.

## Project Structure
```bash
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
```

##### NB:
`index.php` is the entry point of the project. All requests are redirected to the `index.php`. This is achieved through the `.htaccess` file. From there, the appRouter picks up all requests made to the app. 

`init.php` is where all the core and helper classes are "registered".

## Routing

The `/routes` folder contains the route files of the API. By default, the `routes` folder contains an index.php file which is included in `index.php`.
#### NB: Only GET and POST requests are supported currently

The app router can be found in `/src/router/Router.php` and is registered in 

### Simple Routing
**Get Requests**
```php
<?php

  $router->get('/home', function() use($response) {
    return $response->respond(/*data*/);
  });
```

**Post Requests**
```php
<?php

  $router->post('/people/add', function() use($response) {
    return $response->respond(/*data*/);
  });
```
#### NB: Dynamic routes are not currently supported
#### Unsupported
```php
<?php

  $router->get('/user/{id}', function() use($response) {
    return $response->respond(/*data*/);
  });
```

#### Work Around
```php
<?php

  $router->get('/user?id='.$id, function() use($response) {
    return $response->respond(/*data*/);
  });
```


## App Header Configurations
All headers for are defined in `src/config/headers.php`, add or remove headers from this file
```php
<?php
  header('Content-Type: application/json');
  header('Access-Control-Allow-Origin: *');
  // header('Access-Control-Allow-Headers: *');
```


## Core Functionality
Leaf comes along with a lot of helper functions which make development so easy, below is a list of the core functionality

### date functions
Leaf carries a lot of handy functions to help handle date all from the `CustomDate` class initialised in the `init.php` file
[GetDateFromTimeStamp](#Getdatefromtimestamp)

#### GetDateFromTimeStamp
This gets the date in YY-MM-DD format from an existing timestamp
```php
<?php
  $parsedDate = $date->GetDateFromTimeStamp($timestamp);
```


## Handy Functions


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

#### The `docs` for this project are incomplete, use the `readme` instead