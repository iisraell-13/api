<?php
namespace Src\Config;

class DatabaseConnector {

    private $dbConnection = null;

    public function __construct()
    {
        $host = 'zipdb.ith13.com';
        $port = 3306;
        $db   = 'apidb_zd';
        $user = 'zipdev11';
        $pass = 'ThiS1s4fUn4P!.';

        try {
            $this->dbConnection = new \PDO(
                "mysql:host=$host;port=$port;charset=utf8mb4;dbname=$db",
                $user,
                $pass
            );
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }

    public function getConnection()
    {
        return $this->dbConnection;
    }
}