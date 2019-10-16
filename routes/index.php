<?php
	$router->get('/', function() use($response) {
		$res = [
			'message' => 'Welcome to the Leaf PHP starter...you can find a simple demo in `routes/index.php`',
			'first steps' => array(
				'One' => 'Define what you want to use Leaf Starter for (setting up an API or server side rendering is especially easy with Leaf)',
				'Two' => 'Read the documentation, every piece of functionality you need has been explained over there'
			)
		];
		return $response->respond($res);
	});

	$router->get('/home', function() use($response) {
		// there's no need to return the html page since it will display immedietly it loads
		$response->renderHtmlPage('html/home.php');
	});

	$router->get('/books/all', function() use($connection, $response) {
		$books = mysqli_fetch_all(mysqli_query($connection, "SELECT * FROM books"));
		return $response->respond($books);
	});

	$router->get('/date-tests', function() use($response, $date) {
		// some date methods
		$timestamp = $date->timestamp();
		$dates = array();
		$dates['GetDateFromTimeStamp'] = $date->GetDateFromTimeStamp($timestamp);
		$dates['GetEnglishDateFromTimeStamp'] = $date->GetEnglishDateFromTimeStamp($timestamp);
		$dates['GetEnglishTimeStampFromTimeStamp'] = $date->GetEnglishTimeStampFromTimeStamp($timestamp);
		$dates['GetTimeFromTimeStamp'] = $date->GetTimeFromTimeStamp($timestamp);
		return $response->respond($dates);
	});