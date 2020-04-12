<?php
require 'vendor/autoload.php';

\Leaf\App::registerAutoloader();

$app = new \Leaf\App;
$date = new Leaf\Date;
$form = new Leaf\Form;

$app->get("/lol", function() use($form) {
	$form->submit("POST", "/post", [
		"name" => "Mychi",
		"age" => "18",
		"gender" => "male"
	]);
});

$app->post("/post", function() use($app) {
	$app->response->respond([
		$app->request->body(),
		$app->request->headers()
	]);
});

$app->run();
