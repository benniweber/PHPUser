<?php 
/* BIG WARNING: This is an example-file, which shows you how to use PHPUser. Please do not put into your productive environment! */
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>PHPUser - Example</title>
</head> 
<body>
<p>
<?php 

error_reporting(E_ALL);
ini_set('display_errors', 1); //Development
//ini_set('display_errors', 0); //Production

require_once './classes/user.php';
$time=time();

$user = new User();
/*Registration*/
$ret = $user->register('test'.$time, 'test', 'mail'.$time.'@example.com');
echo('Registration: Outcome: '.$ret['outcome'].', message: '.$ret['message'].', id: '.$ret['id']);

echo('<br/>USER-DATA: id: '.$user->getId().', name: '.$user->getName().', pass: '.$user->getPass().', mail: '.$user->getMail().', reg: '.$user->getReg().', session: '.$user->getSession());

/*Login*/
$ret = $user->login('test'.$time, 'test');
//$ret = $user->login('test', 'test');
echo('<br/>Login: Outcome: '.$ret['outcome'].', message: '.$ret['message'].', id: '.$ret['id']);

echo('<br/>USER-DATA: id: '.$user->getId().', name: '.$user->getName().', pass: '.$user->getPass().', mail: '.$user->getMail().', reg: '.$user->getReg().', session: '.$user->getSession().', lastAct: '.$user->getLastAct());


/*Session*/
$ret = $user->checkSession();
echo('<br/>Session: Outcome: '.$ret['outcome'].', message: '.$ret['message'].', id: '.$ret['id']);

echo('<br/>USER-DATA: id: '.$user->getId().', name: '.$user->getName().', pass: '.$user->getPass().', mail: '.$user->getMail().', reg: '.$user->getReg().', session: '.$user->getSession().', lastAct: '.$user->getLastAct());



?>
</p>
</body>
</html>