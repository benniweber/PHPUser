<?php
/**
 * 
 * Configuration Area
 * Lets you configure the behavior of the PHPUser-lib
 */
/* Databasesettings, MAKE SURE TO UPDATE WITH YOUR DB DATA */
define('DBHOST', 'localhost');
define('DBUSER', 'root');
define('DBPASS', '');
define('DBNAME', 'user');

/* Confid for PHPUser */
define('SET_COOKIES', '1'); //Shall i set a cookie, which will make the user stay logged in? Otherwhise you have to implement that...
define('SESSION_VALID_TIME', '1800'); //How long should the session be valid in seconds? (60*30 = 1800 = 30m) 

/* Include Output-Texts (Error/Success-Messages) */
require_once 'texts.php';
?>