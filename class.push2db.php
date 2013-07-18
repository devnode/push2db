<?php

/**
 * push2db
 * 
 * A tool written in PHP to automate the execution of queries across multiple MySQL databases.
 * 
 * LICENSE: Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package    push2db
 * @author     Tom Mombourquette <tom@devnode.com>
 * @copyright  2013 Tom Mombourquette
 * @license    http://www.opensource.org/licenses/mit-license.html  MIT License
 * @link       http://github.com/devnode/push2db
 */

namespace devnode;

class push2db {

    protected $database_regex;
    protected $databases = [];
    protected $databases_skipped = [];
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
        
        $databases = array_diff($this->databases, $this->databases_skipped);

        if (count($databases) > 0) {
            
            foreach ($databases AS $db) {

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
    
    /**
     * Skip databases from the list
     *
     * @param string $database
     * @return null
     */
    public function skipDatabase($database) 
    { 
        if (preg_match('/^[0-9a-zA-Z\_]{1,64}$/', $database) > 0) {
                $this->databases_skipped[] = $database;
        } else {
            die(sprintf("%s() - Invalid database name: `%s`".PHP_EOL, __METHOD__, $database));
        }
    }
    
}