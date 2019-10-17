<?php
	// Custom 404 Handler
    $leaf->set404(function () {
        header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
        echo '404, route not found!';
	});
	
	$leaf->get('/', function() use($response) {
		$res = [
			'message' => 'Welcome to the Leaf PHP starter...you can find a simple demo in `routes/index.php`',
			'first steps' => array(
				'One' => 'Define what you want to use Leaf Starter for (setting up an API or server side rendering is especially easy with Leaf)',
				'Two' => 'Read the documentation, every piece of functionality you need has been explained over there'
			)
		];
		echo $response->respond($res);
	});

	$leaf->get('/home', function() use($response) {
		// there's no need to return the html page since it will display immedietly it loads
		$response->renderHtmlPage('html/home.php');
	});

	$leaf->get('/hello', function () {
        echo '<h1>bramus/router</h1><p>Visit <code>/hello/<em>name</em></code> to get your Hello World mojo on!</p>';
	});

	$leaf->get('/user/{id}', function ($id) {
        echo $id;
	});

	// database example (to use, first define your database connection variables in init.php)
	// $leaf->get('/books/all', function() use($connection, $response) {
	// 	$books = mysqli_fetch_all(mysqli_query($connection, "SELECT * FROM books"));
	// 	echo $response->respond($books);
	// });

	$leaf->get('/date-tests', function() use($response, $date) {
		// some date methods
		$timestamp = $date->timestamp();
		$dates = array();
		$dates['GetDateFromTimeStamp'] = $date->GetDateFromTimeStamp($timestamp);
		$dates['GetEnglishDateFromTimeStamp'] = $date->GetEnglishDateFromTimeStamp($timestamp);
		$dates['GetEnglishTimeStampFromTimeStamp'] = $date->GetEnglishTimeStampFromTimeStamp($timestamp);
		$dates['GetTimeFromTimeStamp'] = $date->GetTimeFromTimeStamp($timestamp);
		echo $response->respond($dates);
	});
