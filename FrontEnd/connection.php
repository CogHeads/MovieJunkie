<?php

define('DB_HOST', 'localhost');
define('DB_NAME', 'database_project');
define('DB_USER','root');
define('DB_PASSWORD','');

$con = mysqli_connect(DB_HOST,DB_USER,DB_PASSWORD,DB_NAME) or die("Failed to connect to MySQL1: " . mysqli_error($con));

?>