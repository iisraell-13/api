<?php

require "../src/config/DatabaseConnector.php";
use Src\Config\DatabaseConnector;

$dbConnection = (new DatabaseConnector())->getConnection();