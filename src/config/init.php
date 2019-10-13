<?php
    $router = new Router(new HttpRequest);

    $database = new Database();
    $connection = $database->connect('PDO');

    $response = new Response();

    $request = new Request();

    $validate = new Validation($response);

    $date = new CustomDate();

    $jwt = new JWT();
