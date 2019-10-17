<?php
    // $host = 'localhost';
    // $user = 'root';
    // $password = '';
    // $dbname = 'books';

    // $database = new \Leaf\Config\Database();
    // $connection = $database->connectMysqli($host, $user, $password, $dbname);
    
    $leaf = new \Leaf\Core\Router();

    $response = new \Leaf\Core\Response();

    $request = new \Leaf\Core\Request();

    $validate = new \Leaf\Core\Validation($response);

    $date = new \Leaf\Core\CustomDate();

    $jwt = new \Leaf\Helpers\JWT();

    $authentication = new \Leaf\Core\Authentication();
