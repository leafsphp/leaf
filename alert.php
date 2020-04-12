<?php
require 'vendor/autoload.php';

\Leaf\App::registerAutoloader();

$app = new \Leaf\App;
$mail = new Leaf\Mail;

$mail->smtp_connect("smtp.gmail.com", 587, true, "mickdd22@gmail.com", "Templerun3");

$mail->basic("From Leaf PHP", "This is the body", "receiver", "Leaf v2.0")->send();