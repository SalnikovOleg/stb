<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<title>Вход в админ страницу</title>
<meta http-equiv="Content-Style-Type" content="text/css" charset="windows-1251" />

<script language="JavaScript">
function checkLogin() { 
	var f = document.getElementById('loginform');

	if ((f.login.value.length < 4) || (f.password.value.length < 4) )
	{	
		alert("Необходимо ввести логин и пароль");
		return false;
	}	
	else
		return true;
}
</script>

<style> 
/* форма логина */
#start_bg{ no-repeat center top; height:400px; width:1000px; padding-top:190px}
#loginform{font-size:16px; font-family:Arial; color:#114088; border: 7px #7e9bc3 double; width:260px; padding:10px}
#loginform  input.text{background:transparent; border:2px solid #7e9bc3; height:18px; width:160px}
#loginform  input.button{border:2px solid #7e9bc3; background:transparent; padding:2px 20px; color:#114088}
#loginform  tr {height:30px}
/* /форма логина */
</style>

</head>
<body>

<center>
<div id="start_bg">
<form name="loginform" id="loginform" action="https://<?php echo $_SERVER['SERVER_NAME'];?>/admin/index.php" method="post" >
Вход в админ страницу
<table>
<tr><td>Логин <td>&nbsp;&nbsp;<input type="text" class="text" id="login" name="login" maxlength="20" value=""><br>
<tr><td>Пароль<td>&nbsp;&nbsp;<input type="password" class="text" id="password" name="password" maxlength="20" value=""><br>
<tr><td align="center" colspan="2"><input class="button" type="submit" value="Вход" >
</table>
</form>
</div>
</center>
</body>
</html>