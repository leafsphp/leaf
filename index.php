<?php
require 'vendor/autoload.php';
require 'app/Test.php';
require 'app/Component.php';

\Leaf\App::registerAutoloader();

$app = new \Leaf\App;

$date = new Leaf\Date;
$form = new Leaf\Form;
$auth = new \Leaf\Auth;
$mail = new Leaf\Mail;

// $app->fs->delete_file("txt.log");

$app->get("/lol", function() use($form) {
	$form->submit("POST", "/post", [
		"name" => "Mychi",
		"age" => "18",
		"gender" => "male"
	]);
});

$app->post("/post", function() use($app, $auth) {
	$app->response->respond([
		$app->request->body(),
		$app->fs->listDir("./")
	]);
});

$app->get("/lw", "Component@trigger");

// $app->get("/lw", function() use($mail, $app) {
// 	$app->blade->configure("app/pages", "app/pages/cache");
// 	$mail->Body = $app->blade->render("mail", [
// 		"title" => "Employment",
// 		"name" => "Michael",
// 		"position" => "maintainer"
// 	]);
// 	$app->response->renderMarkup($mail->Body);
// });

$app->run();
