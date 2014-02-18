<?php
include_once 'conf.php';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html;charset=UTF-8" />
	<title></title>
</head>
<body>
<?php
$reg = new auth();  //~ Создаем новый объект класса
$form = '
	<a href="join.php">Авторизоваться</a><br />
	<form action="" method="post">
		логин <input type="text" name="login" id="" value="'.@$_POST['login'].'" /><br />
		пароль <input type="password" name="passwd" id="" /><br />
		повторите пароль <input type="password" name="passwd2" id="" /><br />
		Почта <input type="text" name="mail" value="'.@$_POST['mail'].'" /><br />
		Пол
				<select name="sex">
					<option value="male">Мужской</option>
					<option value="female">Женский</option>
				</select>
				<br />
		<input type="submit" value="send" name="send" /><br />
	</form>
	';
if (isset($_POST['send'])) {
	if ($reg->reg()) {
		print '
			<h2>Регистрация успешна.</h2>
			Вы можете войти <a href="index.php">авторизоваться</a>.
		';
	} else {
		print $reg->error_reporting();
		print $form;
	}
} else print $form;


//var_dump($_POST);
?>
</body>
</html>
