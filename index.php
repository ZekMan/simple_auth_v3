<?php
include_once 'conf.php';
$r='';

$auth = new auth();
//~ authorization
if (isset($_POST['send'])) {
	if (!$auth->authorization()) {
		$error = $auth->error_reporting();
	}
}

//~ user exit
if (isset($_GET['exit'])) $auth->exit_user();

//~ Check auth
if ($auth->check()) {
	$r.='Hello '.$_SESSION['login_user'].'<br/><a href="?exit">exit</a>';
} else {
	if (isset($error)) $r.=$error.'. <a href="recovery.php">recovery password</a><br/>';

	$r.='
	<br /><a href="join.php">registration</a><br />
	<form action="" method="post">
		login <input type="text" name="login" value="'.@$_POST['login'].'" /><br />
		passwd <input type="password" name="passwd" id="" /><br />
		<input type="checkbox" name="remember" value="on"> remember me
		<input type="submit" value="send" name="send" />
	</form>
	';
}

print $r;

?>
