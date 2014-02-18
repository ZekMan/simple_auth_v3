<?php

class mysql {
	/**
	*	Function connection to MySQL
	* @param	$db_host			domain or ip mysql server (localhost - default)
	* @param	$db_login			user name for connect to mysql server
	* @param	$db_password	password for conntect to mysql server
	* @param	$db_name			mysql database
	*/
	static function connect($db_host, $db_login, $db_passwd, $db_name) {
		mysql_connect($db_host, $db_login, $db_passwd) or die ("MySQL Error: " . mysql_error()); //~ устанавливаем подключение с бд
		mysql_query("set names utf8") or die ("<br>Invalid query: " . mysql_error()); //~ указываем что передаем данные в utf8
		mysql_select_db($db_name) or die ("<br>Invalid query: " . mysql_error()); //~ выбираем базу данных
	}


	/**
	*	MySQL query
	*
	*	@param	$query		Mysql query
	*	@param	$type			Type: num_row, result, assoc or none
	*	@param	$num			Param in mysql_result
	*	@return	boolean		True or False
	*/
	static function query($query, $type=null, $num=null) {
		if ($q=mysql_query($query)) {
			switch ($type) {
				case 'num_row' : return mysql_num_rows($q); break;
				case 'result' : return mysql_result($q, $num); break;
				case 'assoc' : return mysql_fetch_assoc($q); break;
				case 'none' : return $q;
				default: return $q;
			}
		} else {
			return false;
		}
	}

	/**
	*	MySQL data screening
	*
	*	@param	$data			Screening string
	*	@return	string		Result string
	*/
	static function screening($data) {
		$data = trim($data);
		return mysql_real_escape_string($data);
	}

	/**
	 * MySQL data screening array
	 * @papam $data		screening array
	 * @return array	result array
	 */
	static function screening_array($data) {
		foreach ($data as $key=>$value) {
			$tmp[$key]=mysql::screening($value);
		}
		return $tmp;
	}
}

class auth {
	static $error_arr=array();
	static $error='';
	
	/**
	 * This method validate user data
	 * @param		$login			user login
	 * @param		$passwd			user password one
	 * @param		$passwd2		user password two
	 * @param		$mail				user email
	 * @return	bollean			return true or false
	 */
	function check_new_user($login, $passwd, $passwd2, $mail) {
		//~ validate user data
		if (empty($login) or empty($passwd) or empty($passwd2)) $error[]='Все поля обязательны для заполнения';
		if ($passwd != $passwd2) $error[]='Введенные пароли не совпадают';
		if (strlen($login)<3 or strlen($login)>30) $error[]='Длинна логина должна быть от 3 до 30 символов';
		if (strlen($passwd)<3 or strlen($passwd)>30) $error[]='Длинна пароля должна быть от 3 до 30 символов';
		//~ validate email
		if (!filter_var($mail, FILTER_VALIDATE_EMAIL)) $error[]='Не корректный email';
		//~ Checks the user with the same name in the database
		if (mysql::query("SELECT * FROM users WHERE login_user='".$login."';", 'num_row')!=0) $error[]='Пользователь с таким именем уже существует';
		if (mysql::query("SELECT * FROM users WHERE mail_user='".$mail."';", 'num_row')!=0) $error[]='Пользователь с таким email уже существует';

		//~ return error array or TRUE
		if (isset($error)) {
			self::$error_arr=$error;
			return false;
		} else {
			return true;
		}
	}

	/**
	 *	This method is used to register a new user
	 *	@return	boolean or string			return true or html code error
	 */
	function reg() {
		//~ screening input data
		$tmp_arr=mysql::screening_array($_POST);
		$login=$tmp_arr['login'];
		$passwd=$tmp_arr['passwd'];
		$passwd2=$tmp_arr['passwd2'];
		$mail=$tmp_arr['mail'];
		//~ User floor translate to a numeric value
		if ($tmp_arr['sex']=='male') {
			$sex='1';
		} else {
			$sex='2';
		}
		//~ Check valid user data
		if ($this->check_new_user($login, $passwd, $passwd2, $mail)) {
			//~ User data is correct. Register.
			$user_key = $this->generateCode(10);
			$passwd = md5($user_key.$passwd.SECRET_KEY); //~ password hash with the private key and user key
			$query=mysql::query("INSERT INTO `users` (`id_user`, `login_user`, `passwd_user`, `mail_user`, `sex_user`, `key_user`) VALUES (NULL, '".$login."', '".$passwd."', '".$mail."', '".$sex."','".$user_key."');");
			if ($query) {
				return true;
			} else {
				self::$error='Возникла ошибка при регистрации нового пользователя. Свяжитесь с администрацией';
				return false;
			}
		} else {
			return false;
		}
	}


	###
	#	Проверка авторизации
	function check() {
		if (isset($_SESSION['id_user']) and isset($_SESSION['login_user'])) return true;
		else {
			//~ проверяем наличие кук
			if (isset($_COOKIE['id_user']) and isset($_COOKIE['code_user'])) {
				//~ куки есть - сверяем с таблицей сессий
				$db = new mysql(); //~ создаем новый объект класса
				$id_user=$db->screening($_COOKIE['id_user']);
				$code_user=$db->screening($_COOKIE['code_user']);
				if ($db->query("SELECT * FROM `session` WHERE `id_user`=".$id_user.";", 'num_row', '')==1) {
					//~ Есть запись в таблице сессий, сверяем данные
					$data = $db->query("SELECT * FROM `session` WHERE `id_user`=".$id_user.";", 'accos', '');
					if ($data['code_sess']==$code_user and $data['user_agent_sess']==$_SERVER['HTTP_USER_AGENT']) {
						//~ Данные верны, стартуем сессию
						$_SESSION['id_user']=$id_user;
						$_SESSION['login_user']=$db->query("SELECT login_user FROM `users` WHERE  `id_user` = '".$id_user."';", 'result', 0);
						//~ обновляем куки
						setcookie("id_user", $_SESSION['id_user'], time()+3600*24*14);
						setcookie("code_user", $code_user, time()+3600*24*14);
						return true;
					} else return false; //~ данные в таблице сессий не совпадают с куками
				} else return false; //~ в таблице сессий не найден такой пользователь
			} else return false;
		}
	}

	/**
	 * This method performs user authorization
	 * @return boolen			true or false
	 */
	function authorization() {
		//~ screening user data
		$user_data=mysql::screening_array($_POST);
		//~ Find a user with the same name and taking his key
		$find_user=mysql::query("SELECT * FROM `users` WHERE `login_user`='".$user_data['login']."';", 'assoc');
		if (!$find_user) {
			//~ user not found
			self::$error='User not found';
			return false;
		} else {
			//~ user found
			$passwd=md5($find_user['key_user'].$user_data['passwd'].SECRET_KEY); //~ password hash with the private key and user key
			if ($passwd==$find_user['passwd_user']) {
				//~ passwords match
				$_SESSION['id_user']=$find_user['id_user'];
				$_SESSION['login_user']=$find_user['login_user'];
				//~ if user select "remember me"
				if (isset($user_data['remember']) and $user_data['remember']=='on') {
					$cook_code=$this->generateCode(15);
					mysql::query("INSERT INTO `session` (`id_sess` `id_user`, `code_sess`, `user_agent_sess`) VALUES (`null`, '".$find_user['id_user']."', '".$cook_code."', '".$_SERVER['HTTP_USER_AGENT']."');");
					setcookie("id_user", $_SESSION['id_user'], time()+3600*24*30);
					setcookie("code_user", $r_code, time()+3600*24*30);
				}
			} else {
				//~ passwords not match
				self::$error='User not found or password not match';
			}
		}
		
		
		if ($db->query("SELECT * FROM `users` WHERE  `login_user` =  '".$login."' AND  `passwd_user` = '".$passwd."';", 'num_row', '')==1) {
			//~ пользователь найден в бд, логин совпадает с паролем
			$_SESSION['id_user']=$db->query("SELECT * FROM `users` WHERE  `login_user` =  '".$login."' AND  `passwd_user` = '".$passwd."';", 'result', 0);
			$_SESSION['login_user']=$login;
			//~ добавляем/обновляем запись в таблице сессий и ставим куку
			$r_code = $this->generateCode(15);
			if ($db->query("SELECT * FROM `session` WHERE `id_user`=".$_SESSION['id_user'].";", 'num_row', '')==1) {
				//~ запись уже есть - обновляем
				$db->query("UPDATE `session` SET `code_sess` = '".$r_code."', `user_agent_sess` = '".$_SERVER['HTTP_USER_AGENT']."' WHERE `id_user` = ".$_SESSION['id_user'].";", '', '');
			} else {
				//~ записи нету - добавляем
				$db->query("INSERT INTO `session` (`id_user`, `code_sess`, `user_agent_sess`) VALUES ('".$_SESSION['id_user']."', '".$r_code."', '".$_SERVER['HTTP_USER_AGENT']."');", '', '');
			}
			//~ ставим куки на 2 недели
			setcookie("id_user", $_SESSION['id_user'], time()+3600*24*14);
			setcookie("code_user", $r_code, time()+3600*24*14);
			return true;
		} else {
			//~ пользователь не найден в бд, или пароль не соответствует введенному
			if ($db->query("SELECT * FROM  `users` WHERE  `login_user` =  '".$login."';", 'num_row', 0)==1) $error[]='Введен не верный пароль';
			else $error[]='Такой пользователь не существует';
			$_SESSION['error'] = $this->error_print($error);
			return false;
		}
	}

	###
	#	Выход
	function exit_user() {
		//~ разрушаем сессию, удаляем куки и отправляем на главную
		session_destroy();
		setcookie("id_user", '', time()-3600);
		setcookie("code_user", '', time()-3600);
		header("Location: index.php");
	}

	###
	#	Восстановление пароля
	function recovery_pass($login, $mail) {
		$db = new mysql(); //~ создаем новый объект класса
		$login = $db->screening($login);
		$db_inf = $db->query("SELECT * FROM `users` WHERE `login_user`='".$login."';", 'accos', '');
		if ($db->query("SELECT * FROM `users` WHERE `login_user`='".$login."';", 'num_row', '')!=1) {
			//~ не найден такой пользователь
			$error[]='Пользователь с таким именем не найден';
			return $this->error_print($error);
		} else {
			//~ проверка email
			if (!filter_var($mail, FILTER_VALIDATE_EMAIL)) $error[]='Введен не корректный email';
			if ($mail != $db_inf['mail_user']) $error[]='Введенный email не соответствует введенному при регистрации ';
			if (!isset($error)) {
				//~ восстанавливаем пароль
				$new_passwd = $this->generateCode(8);
				$new_passwd_sql = md5($new_passwd.'lol');
				$message = "Вы запросили восстановление пароля на сайте %sitename% для учетной записи ".$db_inf['login_user']." \nВаш новый пароль: ".$new_passwd."\n\n С уважением администрация сайта %sitename%.";
				if (mail($mail, "Восстановление пароля", $message, "From: webmaster@sitename.ru\r\n"."Reply-To: webmaster@sitename.ru\r\n"."X-Mailer: PHP/" . phpversion())) {
					//~ почта отправлена, обновляем пароль в базе
					$db->query("UPDATE `users` SET `passwd_user`='".$new_passwd_sql."' WHERE `id_user` = ".$db_inf['id_user'].";", '', '');
					//~ все успешно - возвращаем положительный ответ
				return 'good';
				} else {
					//~ ошибка при отправке письма
					$error[]='В данный момент восстановление пароля не возможно, свяжитесь с администрацией сайта';
					return $this->error_print($error);
				}
			} else return $this->error_print($error);
		}
	}

 	/**
 	 *	This method generate random string
 	 * @param		$length				int - length string
 	 * @return	string				result random string
 	 */
	function generateCode($length) { 
		$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPRQSTUVWXYZ0123456789"; 
		$code = ""; 
		$clen = strlen($chars) - 1;   
		while (strlen($code) < $length) { 
			$code .= $chars[mt_rand(0,$clen)];   
		} 
		return $code; 
	}


	###
	#	Формирование списка ошибок
	function error_print($error) {
		$r='<h2>Произошли следующие ошибки:</h2>'."\n".'<ul>';
		foreach($error as $key=>$value) {
			$r.='<li>'.$value.'</li>';
		}
		return $r.'</ul>';
	}

	/**
	 *	This method returns the current error
	 */
	function error_reporting() {
		$r='';
		if (mb_strlen(self::$error)>0) {
			$r.=self::$error;
		}
		if (count(self::$error_arr)>0) {
			$r.='<h2>Произошли следующие ошибки:</h2>'."\n".'<ul>';
			foreach(self::$error_arr as $key=>$value) {
				$r.='<li>'.$value.'</li>';
			}
			$r.='</ul>';
		}
		return $r;
	}
}

?>
