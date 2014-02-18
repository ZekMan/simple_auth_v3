<?php
include_once 'conf.php';
$auth = new auth();
$r='';
$form='
	<form action="" method="post">
		Login <input type="text" name="login" id="" value="'.@$_POST['login'].'" /><br />
		E-mail <input type="text" name="mail" value="'.@$_POST['mail'].'" /><br />
		<input type="submit" value="send" name="send" />
	</form>
';

if (isset($_POST['send'])) {
	if ($auth->recovery_pass($_POST['login'], $_POST['mail'])) {
		$r.='A new password has been sent to your inbox. <a href="/index.php">Login</a>';
	} else {
		$r.=$auth->error_reporting().$form;
	}
} else $r.=$form;

print $r;
?>
