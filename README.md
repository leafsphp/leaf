# Leaf PHP Quick Start v1.1.0

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
    $leaf = new Router(new HttpRequest);

    $database = new Database();
    $connection = $database->connectMysqli(...);

    $response = new Response();

    $request = new Request();

    $validate = new Validation($response);

    $date = new CustomDate();

    $jwt = new JWT();
```


## Routing

The `/core` folder now contains the `router.php` file. 

- [New Features](#new-features)
- [404](#handling-404)
- [GET](#get-requests)
- [POST](#post-requests)
- [Dynamic Routing](#dynamic-routing)
- [Sub Routing](#subrouting)

#### new features
Leaf PHP uses a new router version(v1.1.0). In v1.1, all major http requests are supported, effort has been made to keep the syntax as backward compatible as possible. Also, `named parameters` are now supported.
In v1.1.0, `return $response` is no longer supported, `echo $response` is used instead. 

#### Handling 404
```php
<?php
   // Custom 404 Handler
   $leaf->set404(function () {
      header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
      echo '404, route not found!';
   });
```

#### Routing Shorthands
```php
$leaf->get('/pattern', function() { /* ... */ });
$leaf->post('/pattern', function() { /* ... */ });
$leaf->put('/pattern', function() { /* ... */ });
$leaf->delete('/pattern', function() { /* ... */ });
$leaf->options('/pattern', function() { /* ... */ });
$leaf->patch('/pattern', function() { /* ... */ });
```
You can use this shorthand for a route that can be accessed using any method:
```php
$router->all('/pattern', function() { … });
```

Note: Routes must be hooked before `$router->run();` is being called.

Note: There is no shorthand for `match()` as `Leaf/Router` will internally re-route such requrests to their equivalent GET request, in order to comply with RFC2616 (see note).

#### Get Requests
```php
<?php

  $leaf->get('/home', function() use($response) {
    echo $response->respond(/*data*/);
  });
```

#### Post Requests
```php
<?php

  $leaf->post('/people/add', function() use($request, $response) {
     $name = $request->getParam('name');
     echo $response->respond(/*data*/);
  });
```

#### Dynamic routing
Dynamic routing is currently not fully supported, there are still a few problems here and there

**Version 1.1.0 *new***
```php
<?php

  $leaf->get('/user/{id}', function() use($response) {
    echo $response->respond(/*data*/);
  });
```


**Version 1.0.0 *no longer supported***
```php
<?php

  $leaf->get('/user?id='.$id, function() use($response) {
    echo $response->respond(/*data*/);
  });
```


#### Dynamic PCRE-based Route Patterns
This type of Route Patterns contain dynamic parts which can vary per request. The varying parts are named subpatterns and are defined using regular expressions.

Examples:
- /movies/(\d+)
- /profile/(\w+)
Commonly used PCRE-based subpatterns within Dynamic Route Patterns are:
- \d+ = One or more digits (0-9)
- \w+ = One or more word characters (a-z 0-9 _)
- [a-z0-9_-]+ = One or more word characters (a-z 0-9 _) and the dash (-)
- .* = Any character (including /), zero or more
- [^/]+ = Any character but /, one or more

Note: The [PHP PCRE Cheat Sheet](https://www.cs.washington.edu/education/courses/190m/12sp/cheat-sheets/php-regex-cheat-sheet.pdf) might come in handy.


The subpatterns defined in Dynamic PCRE-based Route Patterns are converted to parameters which are passed into the route handling function. Prerequisite is that these subpatterns need to be defined as parenthesized subpatterns, which means that they should be wrapped between parens:
```php
// Bad
$router->get('/hello/\w+', function($name) {
    echo 'Hello ' . htmlentities($name);
});

// Good
$router->get('/hello/(\w+)', function($name) {
    echo 'Hello ' . htmlentities($name);
});
```

Note: The leading `/` at the very beginning of a route pattern is not mandatory, but is recommended.

When multiple subpatterns are defined, the resulting **route handling parameters** are passed into the route handling function in the order they are defined in:

```php 
$router->get('/movies/(\d+)/photos/(\d+)', function($movieId, $photoId) {
    echo 'Movie #' . $movieId . ', photo #' . $photoId);
});
```


#### Dynamic Placeholder-based Route Patterns
This type of Route Patterns are the same as Dynamic PCRE-based Route Patterns, but with one difference: they don't use regexes to do the pattern matching but they use the more easy placeholders instead. Placeholders are strings surrounded by curly braces, e.g. `{name}`. You don't need to add parens around placeholders.

Examples:
- /movies/{id}
- /profile/{username}

Placeholders are easier to use than PRCEs, but offer you less control as they internally get translated to a PRCE that matches any character `(.*)`.
```php
$router->get('/movies/{movieId}/photos/{photoId}', function($movieId, $photoId) {
    echo 'Movie #' . $movieId . ', photo #' . $photoId);
});
```

Note: the name of the placeholder does not need to match with the name of the parameter that is passed into the route handling function:
```php
$router->get('/movies/{foo}/photos/{bar}', function($movieId, $photoId) {
    echo 'Movie #' . $movieId . ', photo #' . $photoId);
});
```

#### Subrouting
```php
<?php
// Subrouting
$leaf->mount('/movies', function () use ($leaf) {
   // will result in '/movies'
   $leaf->get('/', function () {
      echo 'movies overview';
   });
   // will result in '/movies'
   $leaf->post('/', function () {
      echo 'add movie';
   });
   // will result in '/movies/id'
   $leaf->get('/(\d+)', function ($id) {
      echo 'movie id ' . htmlentities($id);
   });
   // will result in '/movies/id'
   $leaf->put('/(\d+)', function ($id) {
      echo 'Update movie id ' . htmlentities($id);
   });
});
```

## Visit [https://github.com/bramus/router](https://github.com/bramus/router) for more info on routing


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

#### timestamp
This gets a random timestamp
```php 
<?php
  $timestamp = $date->timestamp();
```

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
   $leaf->post('/contacts/add', function() use($request) {
```

Here are a couple of methods that come along with `$request` object

```php
<?php
   $leaf->post('/contacts/add', function() use($request) {
      $name = $request->getParam('name');
   });
```
`getParam()` returns the parameter gotten by it's `key` or `selector`

```php
<?php
   $leaf->post('/contacts/add', function() use($request) {
      $data = $request->getBody();
   });
```
`getBody()` returns the `key` => `value` pairs of all the request data


### Response
Response deals with responses and how to handle them....we have a bunch of handy response functionsfor APIs, markup and so much more
To use the response object, you simply need to pass `$response` into your `$route` like this
```php
<?php
   $leaf->post('/contacts/add', function() use($response) {
```

Here are a couple of methods that come along with `$response` object

```php
<?php
   $leaf->post('/contacts/add', function() use($request, $response) {
	  $name = $request->getParam('name');
	  echo $response->respond($data);
   });
```
`respond()` returns json encoded data with a content type of `application/json`, suitable for APIs

```php
<?php
   $leaf->post('/contacts/add', function() use($request, $response) {
	  $name = $request->getParam('name');
	  echo $response->respondWithCode($data, $code);
   });
```
`respondWithCode()` returns json encoded data with a content type of `application/json` with a status code attached, `$code` is optional, and will return 200 if nothing is passed in for `$code`

```php
<?php
   $leaf->post('/contacts/add', function() use($request, $response) {
	  $name = $request->getParam('name');
	  $name ==  null ? $response->throwErr('Name is null', $code) : null;
   });
```
`throwErr()` is our special error handling method that returns an error in JSON format with a status code

```php
<?php
   $leaf->get('/contacts', function() use($response) {
	  $response->renderHtmlPage('linkToPage.html');
   });
```
`renderHtmlPage()` outputs an html page to the screen with a content type of text/html

```php
<?php
   $leaf->get('/contacts', function() use($response) {
	  echo $response->renderMarkup('<b>Hello World</b>');
   });
```
`renderMarkup()` outputs an markup to the screen with a content type of text/html


## Handy Functionality
### Http codes
A couple of http codes have been defined in `constants.php`, you can use them anywhere in your app by just calling the name.
```php
<?php
   $leaf->get('/me', function() use($response) {
	   echo $response->respondWithCode('Something to output', SUCCESS);
   });
```
```php
<?php
   $leaf->get('/me', function() use($response) {
	   echo $response->throwErr('Name is null', RESOURCE_NOT_FOUND);
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
- [generateSimpleToken](#generatesimpletoken)
- [generateToken](#generatetoken)
- [validateToken](#validatetoken)
- [getBearerToken](#getbearertoken)
- [getAuthorizationHeader](#getauthorizationheader)

#### generateSimpleToken
This method generates a new JSON Web Token
```php
<?php
   $token = $authentication->generateSimpleToken('user id to encode', 'a secret phrase to use');
```

#### generateToken
This method generates a new JSON Web Token
```php
<?php
   $token = $authentication->generateToken('user id to encode', $expires_at, 'a secret phrase to use', $iss);
```
the `$iss` has a default value of localhost.
the `$expires_at` field takes a `number` and adds it to the `current time`

#### validateToken
This method is used to confirm the identity of a token from an authorization `header`
```php
<?php
   $authentication->validateToken();
```
**This feature is still under development**

#### getBearerToken
This method is used to get the **bearer token** from an authorization `header`
```php
<?php
   $token = $authentication->getBearerToken();
```
**This feature is still under development**

#### getAuthorizationHeader
This method is used to an authorization `header`
```php
<?php
   $authHeader = $authentication->getAuthorizationHeader();
```
**This feature is still under development**


## Database connection

In the `src/config/init.php`, connection variables are declared at the top of the file, enter your own details for your database.

```php
<?php
  $host = 'localhost';
  $user = 'root';
  $password = '';
  $dbname = 'books';
```

In `db.php` provision has been made for both PDO and mysqli. 

```php
$database = new Database();
$connection = $database->connectMysqli($host, $user, $password, $dbname);
```
**to use mysqli**

```php
$database = new Database();
$connection = $database->connectPDO($host, $dbname, $user, $password);
```
**to use PDO**

To use the connection object inside a route(`$leaf`) use:
```php
<?php
   $leaf->post('/users/add', function() use($connection) {
      // your code
   });
```

#### The `docs` for this project are incomplete, use the `readme` instead