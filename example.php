<?php

include('class.push2db.php');

use devnode\push2db;

$push2db = new push2db('localhost', 'root', 'password');
$push2db->setDatabaseRegex("/^user_[0-9]{6}$/");
$push2db->addDatabase('user_000001_custom');

$sql = file_get_contents("example.sql");

$push2db->execute($sql);
