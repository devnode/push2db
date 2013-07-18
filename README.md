push2db
=======

A tool written in PHP to automate the execution of queries across multiple MySQL databases. 

###### Features
+ Dynamic database selection using regular expressions
+ Manually add or skip specific databases 
+ Validates database names against MySQL standards

***

###### Example

    <?php

    include('class.push2db.php');

    use devnode\push2db;
    
    // connect to server
    $push2db = new push2db('localhost', 'root', 'password'); 
    
    // select all user databases
    $push2db->setDatabaseRegex("/^user_[0-9]{6}$/");
    
    // add a specific database
    $push2db->addDatabase('user_custom'); 
    
    // skip a specific database
    $push2db->skipDatabase('user_000001'); 
    
    // feed me some sql 
    $sql = file_get_contents("example.sql");
    
    // and away we go
    $push2db->execute($sql);
    
***

###### Tested on PHP 5.4 - Released under the MIT License 
