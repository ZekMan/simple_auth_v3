<?php
include_once 'conf.php';
$auth = new auth();
$form = '
	<br /><a href="/index.php">Login</a><br />
	<form action="" method="post">
		Login <input type="text" name="login" id="" value="'.@$_POST['login'].'" /><br />
		Password <input type="password" name="passwd" id="" /><br />
		Password again <input type="password" name="passwd2" id="" /><br />
		E-mail <input type="text" name="mail" value="'.@$_POST['mail'].'" /><br />
		Sex
				<select name="sex">
					<option value="male">Male</option>
					<option value="female">Female</option>
				</select>
				<br />
		<input type="submit" value="send" name="send" /><br />
	</form>
	';
if (isset($_POST['send'])) {
	if ($auth->reg()) {
		print '
			Registration successful. <a href="index.php">Login</a>.
		';
	} else {
		print $auth->error_reporting();
		print $form;
	}
} else print $form;
?>
