<?php
define('MAIN_DIR', substr(dirname(__FILE__), 0, strpos(dirname(__FILE__), 'admin')-1 ) );

include "../includes/start.php";
include "../includes/classes/admin.class.php";

// ���������� ������������� �������
if (isset($_GET['function']))
{
	$func = $_GET['function'];
	if (function_exists($func) == false && file_exists(DIR_FUNCTIONS.$func.".php") )
	{
		include DIR_FUNCTIONS.$func.".php";
	}
	
	$result = $func($_GET);
	
	echo $result;
}

// ���������� ������ �������������� �������
if (isset($_GET['module']))
{
	$module = $db->get_moduleByName($_GET['module'], ADMIN_DOC); 
	
	if (!isset($module['name']))
		$module['name'] = substr($_GET['module'], 0,20);
	
	// ���������� ����� � ��������� ������
	if (class_exists($module['name']) == false && file_exists(DIR_MODULES.$module['name'].".php") !== false)
	{	
		include DIR_MODULES.$module['name'].".php";
	}

	if (class_exists($module['name']) == false) return;
	
	$Module = new $module['name']($db, $module);
		
	if ( isset($_GET['method']) ) $method = $_GET['method'];

	echo $Module->$method($_GET);
}  

?>