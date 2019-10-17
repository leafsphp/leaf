<?php
    // $host = 'localhost';
    // $user = 'root';
    // $password = '';
    // $dbname = 'books';

    // $database = new \Leaf\Database();
    // $connection = $database->connectMysqli($host, $user, $password, $dbname);
    
    $leaf = new \Leaf\Router();

    $response = new \Leaf\Response();

    $request = new \Leaf\Request();

    $validate = new \Leaf\Validation($response);

    $date = new \Leaf\CustomDate();

    $jwt = new \Leaf\JWT();

    $authentication = new \Leaf\Authentication();
