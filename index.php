<?php

	define('MAIN_DIR', dirname(__FILE__));
	
	require("includes/start.php");

	// загрузка основных параметров непривязаных к языку
	$p = array('prefix'=>'');
	$P = new Params($db, $p);
	$P->LoadParams();

	// индекс url модуля в массиве запроса
	if (MULTILANGUAGE == 0) define('MODULEINDEX', 1);
	else define('MODULEINDEX', 2);
	
	// проверка url на предмет несоответствия правилам движка
	checkRequestUri($db, $_SERVER['REQUEST_URI']);
	
	$query = explode("/", $_SERVER['REQUEST_URI']);
	
	// определение текущего языка из строки запроса
	$L = new Language($db, $query);
	$result =$L->SelectLanguage();
	unset($L);

	// определение типа докумнета
	if (!isset($query[MODULEINDEX]))
		define('DEFAULT_DOC', 1);
	else	
	switch (trim($query[MODULEINDEX]))
	{	
		case '': define('DEFAULT_DOC', 1);
			break;
		default : define('DEFAULT_DOC', 3);
	}

	// загрузка параметров для текущего языка
	$P->LoadParamsForLang();
	
/*	$p = null;
	$С = new Currency($db, $p);
	$С->SelectCurrency();
	unset($C);
*/
	// проверка пользователя создание новой гостевой записи
	$User = new User($db, true);
	$User->CheckNewGuest();

 	$Doc = new Document($db, DEFAULT_DOC);

	$Doc->LoadModule();
	$Doc->LoadBoxes();
	$Doc->AssignBoxes();

	require DIR_INCLUDES."html_header.php"; 

	$Doc->Display();

	require DIR_INCLUDES."html_footer.php";
	
	// лог sql запросов LOGGED  = true ведение лога запросов
	if (LOGGED) $db->queryLog();

?>