# Petal
Petal is created to make using CURL in PHP much easier: as easy as axios is in JavaScript. Petal uses a structure very much similar to leaf's route
```php
$petal->get('https://api.movies.com/movies/all', function() use($petal) {
	$res = $petal->getResponse();
	$movies = $res['data']['movies'];
	// or
	$movies = $petal->getResponseParam('movies');
});
```
Still under development😅😅...not ready for use....don't use yet😅😅😅


