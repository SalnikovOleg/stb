<?php
// попыкта найти ссылку в таблице references и преобразовать $_SERVER['REQUEST_URI'] к виду ru/module/page
// в переменную $_SERVER['SEARCHED_URL'] засунуть ссылку если найдено и именно по ней делать выборку из бд
function checkRequestUri(&$db, $uri)
{
	// удалить из запроса все параметры
    if (strpos($_SERVER['REQUEST_URI'], '?') !== false)
	    $_SERVER['REQUEST_URI'] = substr($_SERVER['REQUEST_URI'], 0, strpos($_SERVER['REQUEST_URI'], '?'));

	// запомнить значение реального REQUEST_URI  
	$_SESSION['REQUEST_URI'] = $_SERVER['REQUEST_URI'];
		
	// получить список подмен адресов для модулей
	$query_list = readConfig(MAIN_DIR.'/conf/mapping.cfg');
	//array('articles'=>'articles.html');

	$query = explode("/", $_SERVER['REQUEST_URI']);
	if ($query[1] == "") return; 	// исключая главную
	
	$query = substr($_SERVER['REQUEST_URI'], 1);
	if ($query[strlen($query)-1] == '/') $query = substr($query, 0, strlen($query)-1); 
	
	$sql = "SELECT m.id, m.url as module, l.url as lang FROM `references` r
	INNER JOIN a_modules m ON m.id = r.module_id
	INNER JOIN a_language l ON l.id = r.lang
	WHERE r.url = '".$query."'";
	
	$row = $db->db_get_array($sql);
	
	if ($row != null){
		$lang = "";
		if (MULTILANGUAGE == 1)
			$lang = $row['lang'].'/';

		$url = array_search($query, $query_list);
		if ($url !== false){
			$_SERVER['REQUEST_URI'] = '/'.$lang.$row['module'];
			return;
		}
			
		$_SERVER['SEARCHED_URL'] = $query;
		$_SERVER['REQUEST_URI'] = '/'.$lang.$row['module'].'/'.$query;
	}
}


// прочитать конфиг файл
function readConfig($file)
{
	$reuslt = array();
	$list = file($file);
	foreach ($list as $str) {
		$row = trim($str);
		if ($row[0] == '#') continue;
		$d = strpos($row, '=');
		if ($d === false)
			$result[$row] = $row;
		else
			$result[substr($row, 0, $d)] = substr($row, $d+1);
	}

	return $result;
}

// получить смапленную ссылку по урл модуля
function getMappedUrl($value)
{
	$urls = readConfig(MAIN_DIR.'/conf/mapping.cfg');
	if (isset($urls[$value]))
		return $urls[$value];
	else
	return '';	
}

// проверить на предмет 404
function check404($url)
{
	$urls = readConfig(MAIN_DIR.'/conf/404url.cfg');

	return array_search($url, $urls); 
}
?>
