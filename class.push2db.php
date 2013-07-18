<?php

namespace devnode;

class push2db {

    protected $database_regex;
    protected $databases = [];
    protected $host;
    protected $password;
    protected $sql;
    protected $username;
    
    public function __construct($host, $username, $password)
    {
        $this->host = $host;
        $this->username = $username;
        $this->password = $password;
    }
    
    /**
     * Add database to the list
     *
     * @param string $database
     * @return null
     */
    public function addDatabase($database) 
    { 
        if (preg_match('/^[0-9a-zA-Z\_]{1,64}$/', $database) > 0) {
            $this->databases[] = $database;
        } else {
            die(sprintf("%s() - Invalid database name: `%s`".PHP_EOL, __METHOD__, $database));
        }
    }
    
    /**
     * Connect to database server
     *
     * @return null
     */
    private function connect()
    {
        try {
            $pdo = new \PDO("mysql:host=".$this->host, $this->username, $this->password);
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        } catch(\PDOException $e) { 
            die(sprintf("%s() - %s".PHP_EOL, __METHOD__, $e->getMessage()));
        }
        
        return $pdo;
    }
    
    /**
     * Execute SQL on each database
     *
     * @param string $sql
     * @return boolean
     */
    public function execute($sql)
    {
        $pdo = $this->connect();
        $this->findDatabases($pdo);
        
        if (count($this->databases) > 0) {
            
            foreach ($this->databases AS $db) {

                $pdo->exec("USE `$db`;");
                
                try {
                    $pdo->exec($sql);
                } catch (\PDOException $e) {
                    echo sprintf("%s() - `%s` - %s".PHP_EOL, __METHOD__, $db, $e->getMessage());
                }
                
            }
            
        } else {
            die(sprintf("%s() - Database not defined.".PHP_EOL, __METHOD__));
        }
    }
    
    /**
     * Find databases by regular expression
     *
     * @param PDO $pdo
     * @return null
     */
    private function findDatabases(\PDO $pdo)
    {
        if (isset($this->database_regex)) {
            
            try {
                $stmt = $pdo->prepare("SHOW DATABASES");
                $stmt->execute();

                while ($db = $stmt->fetchColumn(0)) {
                    if (preg_match($this->database_regex, $db) > 0) {
                        $this->addDatabase($db);
                    }
                }
            } catch (\PDOException $e) {
                die(sprintf("%s() - %s".PHP_EOL, __METHOD__, $e->getMessage()));
            }
            
        }
    }
    
    /**
     * Set the database regular expression
     *
     * @param string $regex
     * @return null
     */
    public function setDatabaseRegex($regex) 
    { 
        $this->database_regex = $regex;
    }
    
}