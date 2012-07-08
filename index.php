<?php 

error_reporting(E_ALL);
ini_set('display_errors', 1); //Development
//ini_set('display_errors', 0); //Production

require_once './classes/user.php';

$user = new User();
$ret = $user->register('test', 'test', 'mail@example.com');

echo('Registration: Outcome: '.$ret['outcome'].', message: '.$ret['message']);



?>