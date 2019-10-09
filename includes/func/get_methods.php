<?php
//выборка списка методов для модуля из бд a_module_methodds
function get_methods(&$params)
{
	global $db;
	$list = $db->get_method_list($params['id']);
		$result = select_list($list, 'method_id', "", 0, -1);
	echo $result;
}

//подключение класса по имени, должен существовать файл с именем $name.php и клса с именем $name
function includeModule($name, $path="")
{
	if (class_exists($name) == false )
		if ( file_exists(DIR_MODULES.$path.$name.".php") !== false)
		{	
			include DIR_MODULES.$path.$name.".php";
			return true;
		}	
		else
			return false;
	else
		return true;
}
?>