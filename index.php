<?php
require 'vendor/autoload.php';

\Leaf\App::registerAutoloader();

$app = new \Leaf\App;
$date = new Leaf\Date;
$form = new Leaf\Form;
$auth = new \Leaf\Auth;

$app->get("/lol", function() use($form) {
	$form->submit("POST", "/post", [
		"name" => "Mychi",
		"age" => "18",
		"gender" => "male"
	]);
});

$app->post("/post", function() use($app, $auth) {
	$app->response->respondWithCode([
		$app->request->body()
	], 511, true);
});

$app->run();
