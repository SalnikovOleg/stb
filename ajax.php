<?php
	define('MAIN_DIR', dirname(__FILE__));
	define('DEFAULT_DOC', 1);
	
	require("includes/start.php");
	
	$p = null;
	$P = new Params($db, $p);
	$P->LoadParams();	
	
	define('CURRENT_TEMPLATE', MAIN_TEMPLATE."/");

// реализация запрашиваемой функции
if (isset($_GET['function']))
{
	$func = substr($_GET['function'],0,20);
	if (function_exists($func) == false && file_exists(DIR_FUNCTIONS.$func.".php") )
	{
		include DIR_FUNCTIONS.$func.".php";
	}
	
	$result = $func($_GET);
	
	echo $result;
}

// реализация метода запрашиваемого объекта
if (isset($_GET['module']))
{
	$module = $db->get_moduleByName($_GET['module'], 1); 
	if ($module == null)
		$module['name'] = substr($_GET['module'],0,20);
	
	$path="";
	if (isset($_GET['path'])) $path=substr($_GET['path'],0, 20).'/';
	
	// подлючение файла с описанием модуля
	if (class_exists($module['name']) == false && file_exists(DIR_MODULES.$path.$module['name'].".php") !== false)
	{	
		include DIR_MODULES.$path.$module['name'].".php";
	}

	$Module = new $module['name']($db, $module);

	if (isset($_POST['action']))
	{
		$method = $_POST['action'];
		$Module->$method($_POST);
	}	

	if ( isset($_GET['method']) ) 
	{
		$method = $_GET['method'];
		echo $Module->$method($_GET);
	}
}  
?>