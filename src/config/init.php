<?php
    // $host = 'localhost';
    // $user = 'root';
    // $password = '';
    // $dbname = 'books';

    // $database = new Database();
    // $connection = $database->connectMysqli($host, $user, $password, $dbname);
    
    $leaf = new \Leaf\Router\Router();

    $response = new Response();

    $request = new Request();

    $validate = new Validation($response);

    $date = new CustomDate();

    $jwt = new JWT();

    $authentication = new Authentication();
