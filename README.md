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
|   |	  +-- db.php
|   |	  +-- headers.php
|   |	  +-- init.php
|   +-- core
|   |	  +-- date.php
|   |	  +-- fieldValidate.php
|   |	  +-- request.php
|   |	  +-- respond.php
|   +-- helpers
|   |	  +-- constants.php
|   |	  +-- jwt.php 
|   +-- router
|   |	  +-- Irequest.php
|   |	  +-- Request.php
|   |	  +-- Router.php
```


## index.php
`index.php` is the entry point of the project. All requests are redirected to the `index.php`. This is achieved through the `.htaccess` file. From there, the app Router picks up all requests made to the app. Every file is imported into the index.php file for use.

```php
   <?php
    require_once __DIR__ . '/src/router/Request.php';
    require_once __DIR__ . '/src/router/Router.php';

    // core modules
    require __DIR__ . '/src/core/respond.php';
    require __DIR__ . '/src/core/request.php';
    require __DIR__ . '/src/core/fieldValidate.php';
    require __DIR__ . '/src/core/date.php';

    // helpers    
    require __DIR__ . '/src/helpers/constants.php';
    require __DIR__ . '/src/helpers/jwt.php';

    // config files
    require __DIR__ . '/src/config/db.php';
    require __DIR__ . '/src/config/headers.php';
    require __DIR__ . '/src/config/init.php';

    // routes
    require __DIR__ . '/routes/index.php';
```


## init.php
`init.php` is where all the core and helper classes are "registered". Every class in Leaf is called in `init.php`, you can add or remove classes from the `init.php` file.
```php
   <?php
    $router = new Router(new HttpRequest);

    $database = new Database();
    $connection = $database->connect('PDO');

    $response = new Response();

    $request = new Request();

    $validate = new Validation($response);

    $date = new CustomDate();

    $jwt = new JWT();
```


## Routing

The `/routes` folder contains the route files of the API. By default, the `routes` folder contains an index.php file which is included in `index.php`.
#### NB: Only GET and POST requests are supported currently

- [GET](#get-requests)
- [POST](#post-requests)
- [Dynamic Routing](#dynamic-routing)

#### Get Requests
```php
<?php

  $router->get('/home', function() use($response) {
    return $response->respond(/*data*/);
  });
```

#### Post Requests
```php
<?php

  $router->post('/people/add', function() use($response) {
    return $response->respond(/*data*/);
  });
```
#### Dynamic routing
Dynamic routing is currently not fully supported, there are still a few problems here and there

**Unsupported**
```php
<?php

  $router->get('/user/{id}', function() use($response) {
    return $response->respond(/*data*/);
  });
```

**Work Around**
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

- [GetDateFromTimeStamp](#getdatefromtimestamp)
- [GetMonthFromNumber](#getmonthfromnumber)
- [GetDayFromNumber](#getdayfromnumber)
- [GetEnglishDateFromTimeStamp](#getenglishdatefromtimestamp)
- [GetEnglishTimeStampFromTimeStamp](#getenglishtimestampfromtimestamp)
- [GetTimeFromTimeStamp](#gettimefromtimestamp)

#### GetDateFromTimeStamp
This gets the date in YYYY-MM-DD format from an existing timestamp
```php
<?php
  $parsedDate = $date->GetDateFromTimeStamp($timestamp);
```

#### GetMonthFromNumber
This gets the month from a number (0-11)
```php
<?php
  $parsedDate = $date->GetMonthFromNumber($number);
```

#### GetDayFromNumber
This gets the day from a number (1-7)
```php
<?php
  $parsedDate = $date->GetDayFromNumber($number);
```

#### GetEnglishDateFromTimeStamp
This gets the date in the format (MM DD, YYYY) from a timestamp
```php
<?php
  $parsedDate = $date->GetEnglishDateFromTimeStamp($timestamp);
```

#### GetEnglishTimeStampFromTimeStamp
This gets the date in the format (DD MM, YYYY HH:MM:SS) from a timestamp
```php
<?php
  $parsedDate = $date->GetEnglishTimeStampFromTimeStamp($timestamp);
```

#### GetTimeFromTimeStamp
This gets the time in the format (HH:MM:SS) from a timestamp
```php
<?php
  $parsedDate = $date->GetTimeFromTimeStamp($timestamp);
```


### Field Validation
Field Validation takes a field as a parameter and does basic validation on them, there are only two stable validation  tests
```php
<?php
   // checks for empty state and outputs error message or returns $field
   $validate->isEmpty($field, 'Message to display if validation test fails and is optional');
   $validate->isEmptyOrNull($field, 'Message to display if validation test fails and is optional');
```
`isEmpty` checks whether field is empty or not

`isEmptyOrNull` checks whether the field is empty or `null`


### Request
The request section basically deals with requests made to the app, so far, there are only two functions chained to the Request class. 
To use the request object, you simply need to pass `$request` into your `$route` like this
```php
<?php
   $router->post('/contacts/add', function() use($request) {
```

Here are a couple of methods that come along with `$request` object

```php
<?php
   $router->post('/contacts/add', function() use($request) {
      $name = $request->getParam('name');
   });
```
`getParam()` returns the parameter gotten by it's `key` or `selector`

```php
<?php
   $router->post('/contacts/add', function() use($request) {
      $data = $request->getBody();
   });
```
`getBody()` returns the `key` => `value` pairs of all the request data


### Response
Response deals with responses and how to handle them....we have a bunch of handy response functionsfor APIs, markup and so much more
To use the response object, you simply need to pass `$response` into your `$route` like this
```php
<?php
   $router->post('/contacts/add', function() use($response) {
```

Here are a couple of methods that come along with `$response` object

```php
<?php
   $router->post('/contacts/add', function() use($request, $response) {
	  $name = $request->getParam('name');
	  $response->respond($data);
   });
```
`respond()` returns json encoded data with a content type of `application/json`

```php
<?php
   $router->post('/contacts/add', function() use($request, $response) {
	  $name = $request->getParam('name');
	  $response->respondWithCode($data, $code);
   });
```
`respondWithCode()` returns json encoded data with a content type of `application/json` with a status code attached, `$code` is optional, and will return 200 if nothing is passed in for `$code`

```php
<?php
   $router->post('/contacts/add', function() use($request, $response) {
	  $name = $request->getParam('name');
	  $name ==  null ? $response->throwErr('Name is null', $code) : null;
   });
```
`throwErr()` is our special error handling method that returns an error in JSON format with a status code

```php
<?php
   $router->get('/contacts', function() use($response) {
	  $response->renderHtmlPage('linkToPage.html');
   });
```
`renderHtmlPage()` outputs an html page to the screen with a content type of text/html

```php
<?php
   $router->get('/contacts', function() use($response) {
	  $response->renderMarkup('<b>Hello World</b>');
   });
```
`renderMarkup()` outputs an markup to the screen with a content type of text/html


## Handy Functionality
### Http codes
A couple of http codes have been defined in `constants.php`, you can use them anywhere in your app by just calling the name.
```php
<?php
   $router->get('/me', function() use($response) {
	   return $response->respondWithCode('Something to output', SUCCESS);
   });
```
```php
<?php
   $router->get('/me', function() use($response) {
	   return $response->throwErr('Name is null', RESOURCE_NOT_FOUND);
   });
```

#### All available codes
- REQUEST_METHOD_NOT_VALID
- REQUEST_CONTENTTYPE_NOT_VALID
- REQUEST_NOT_VALID
- API_PARAM_REQUIRED
- INVALID_USER_PASS
- USER_NOT_ACTIVE
- RESOURCE_ALREADY_EXISTS
- RESOURCE_NOT_FOUND
- SUCCESS
- AUTHORIZATION_HEADER_NOT_FOUND
- ACCESS_TOKEN_ERROR

### JSON Web Tokens(JWT)
Leaf provides you with the `$jwt` object which includes various methods for creating and parsing token data....but we do not advice directly using the `$jwt` object. For this reason, a special `$authentication` object has been created to work with all the $jwt data.

`$authentiacation` methods:
- [generateToken](#generatetoken)
- [validateToken](#validatetoken)
- [getBearerToken](#getbearertoken)
- [getAuthorizationHeader](#getauthorizationheader)

#### generateToken
This method generates a new JSON Web Token
```php
<?php
   $token = $authentication->generateToken('user id to encode', 'a secret phrase to use');
```

#### validateToken
This method is used to confirm the identity of a token from an authorization `header`
```php
<?php
   $authentication->validateToken();
```

#### getBearerToken
This method is used to get the **bearer token** from an authorization `header`
```php
<?php
   $token = $authentication->getBearerToken();
```

#### getAuthorizationHeader
This method is used to an authorization `header`
```php
<?php
   $authHeader = $authentication->getAuthorizationHeader();
```


## Database connection

In the `src/config/db.php`, connection variables are declared at the top of the file, enter your own details for your database.

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

In `db.php` provision has been made for both PDO and mysqli, by default, the connection type is PDO. To change that, head to the `init.php` file and locate the **database class call**, a single parameter is passed into it which is the connection type

```php
<?php
    $router = new Router(new HttpRequest);

    $database = new Database();
    $connection = $database->connect('PDO');
```

use `connect('PDO);` for a PDO connection and `connect(mysqli)` for and mysqli connection

#### The `docs` for this project are incomplete, use the `readme` instead