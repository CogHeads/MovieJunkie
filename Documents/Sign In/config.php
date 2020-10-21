<?php
/* Database credentials. Assuming you are running MySQL
server with default setting (user 'root' with no password) */
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', 'Wethebestmusix.123');
define('DB_NAME', 'demo');
 
require_once "../db_relay.php";

/* Attempt to connect to MySQL database */
$DB_Manager = new DB_Relay(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
$link = $DB_Manager->GetLink();
?>
