<?php
	session_start();

	// параметры подключения
	require_once(MAIN_DIR."/includes/connect.php");
  
    // folders configuration
	require_once(MAIN_DIR."/includes/configure.php");  
	
	// классы для работы с бд
	require_once(DIR_CLASSES."db.class.php");
	require_once(DIR_CLASSES."db_mysql.class.php");
	require_once(DIR_CLASSES."db_mysql_querys.class.php");
	
	// объект подключения к бд   
    $db = new DB_MySql_Querys();
	
	// получить имя основного домена если таковое задано
	$hostName = $db->get_HOST();	
	
	// редирект на нужный домен если вход был не по тому домену которрый задан
	if ($hostName != null && $_SERVER['SERVER_NAME'] != $hostName) 
	{
		//$_SESSION['REQUEST_URI'] - хранит реальную строку ссылки
		header ('HTTP/1.1 301 Moved Permanently');
                if (!isset($_SESSION['REQUEST_URI']))
		  header("Location: https://".$hostName.$_SERVER['REQUEST_URI']);
                else
		  header("Location: https://".$hostName.$_SESSION['REQUEST_URI']);

		exit();
	}

   // подключение основных классов
	require_once(DIR_CLASSES."document.class.php");
	require_once(DIR_CLASSES."template.class.php");  
	require_once(DIR_CLASSES."module.class.php");
	require_once(DIR_CLASSES."boxes.class.php");
	require_once(DIR_CLASSES."404.class.php");
	require_once(DIR_CLASSES."user.class.php");
	require_once(DIR_CLASSES."pagestree.class.php");
	require_once(DIR_MODULES."Language.php");
	require_once(DIR_MODULES."Params.php");
	require_once(DIR_CLASSES."image_convert.class.php");

	// подключение функций
	require_once(DIR_FUNCTIONS.'create_href_list.php');
	require_once(DIR_TOOLS."securimage/securimage.php");		
	require_once(DIR_FUNCTIONS."get_captcha_input.php");
	require_once(DIR_FUNCTIONS."send_mail.php");
	require_once(DIR_FUNCTIONS."array_func.php");
	require_once(DIR_FUNCTIONS."file_func.php");
	require_once(DIR_FUNCTIONS."convert_func.php");
	require_once(DIR_FUNCTIONS."date_func.php");
	require_once(DIR_FUNCTIONS."create_input.php");
	require_once(DIR_FUNCTIONS."select_list.php");
	require_once(DIR_FUNCTIONS."get_methods.php");
	require_once(DIR_FUNCTIONS."get_ip.php");
	require_once(DIR_FUNCTIONS."security_check.php");
	require_once(DIR_FUNCTIONS."checkRequestUri.php");
	require_once(DIR_FUNCTIONS."navigator_func.php");	
	require_once(DIR_FUNCTIONS."tools.php");	
?>
