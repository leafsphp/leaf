<?php
    $host = 'localhost';
    $user = 'root';
    $password = '';
    $dbname = 'books';

    $leaf = new \Leaf\Router\Router();

    $database = new Database();
    $connection = $database->connectMysqli($host, $user, $password, $dbname);

    $response = new Response();

    $request = new Request();

    $validate = new Validation($response);

    $date = new CustomDate();

    $jwt = new JWT();

    $authentication = new Authentication();
