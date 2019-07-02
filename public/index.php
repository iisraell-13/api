<?php
    require "../bootstrap.php";
    require "../src/controller/PhonebookController.php";
    use Src\Controller\PhonebookController;

    header("Access-Control-Allow-Origin: *");
    header("Content-Type: application/json; charset=UTF-8");
    header("Access-Control-Allow-Methods: POST,GET,PUT,DELETE, PATCH");
    header("Access-Control-Allow-Headers: Content-Type, X-Requested-With");

    $params = null;
    $url = explode( '/', parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) );

    if ($url[1] !== 'person') {
        header("HTTP/1.1 404 Not Found");
        exit();
    }

    if (isset($url[2]) && (int) $url[2]) {
        $params = (int) $url[2];
    } elseif (isset($url[2]) && isset($url[3])) {
        $params['field'] = $url[2];
        $params['value'] = $url[3]; 
    }

    $controller = new PhonebookController($dbConnection, $_SERVER["REQUEST_METHOD"], $params);
    $controller->processRequest();