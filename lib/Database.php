<?php
final class Database {
    public static $db_config = array(
        'connection' => 'mysql',
        'host' => 'localhost',
        'database' => '_',
    
        'username' => '_',
        'password' => '_'

        /* USE CREDENTIALS OF YOUR DATABASE (WITH IMPORTED db/dump.sql)*/
    );
    public static $pdo = null;

    public static function connect() {
        $connectString = Database::$db_config['connection'].':host='.Database::$db_config['host'].';dbname='.Database::$db_config['database'];
        
        try {
            Database::$pdo = new PDO($connectString, Database::$db_config['username'], Database::$db_config['password']);
            return true;
        } 
        catch (PDOException $e) {
            return false;
        }
    }
    public static function tryConnect() {
        $connectString = Database::$db_config['connection'].':host='.Database::$db_config['host'].';dbname='.Database::$db_config['database'];
        
        try {
            Database::$pdo = new PDO($connectString, Database::$db_config['username'], Database::$db_config['password']);
            return array(
                'status' => 'success'
            );
        } 
        catch (PDOException $e) {
            return array(
                'status' => 'fail',
                'message' => strval($e)
            );
        }
    }

    public static function execute(string $query, array $vars = []) {
        $stmt = Database::$pdo->prepare($query);
        $stmt->execute($vars);
    }
    public static function executeFetch(string $query, array $vars = []) {
        $stmt = Database::$pdo->prepare($query);
        $stmt->execute($vars);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public static function executeFetchAll(string $query, array $vars = []) {
        $stmt = Database::$pdo->prepare($query);
        $stmt->execute($vars);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}