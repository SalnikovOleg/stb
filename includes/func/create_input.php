<?php
//функции создания элементов ввода для параметров
// cоздание элемента ввода input type="text"
// $params[index]   indexes - name, params_type, source, value, sql, array, input_type, title
function createInput(&$params)
{
	if ($params['params_type'] == 'text') $maxlength = 50;
	else $maxlength = 10;
	
	if ($params['source'] == 'func')
		$params['value'] = execFunc($params['value']);
	if ($params['source'] == 'session')	
		$params['value'] = $_SESSION[$params['value']];
	
	$script="";
	if ($params['params_type'] == 'date')
		$script = '<script type="text/javascript">calendar.set("'.$params['name'].'");</script>';
		
	$result = '<table class="parameter"><tr><td class="title">'.$params['title'].'</td><td class="value"><input type="text" id="'.$params['name'].'" name="'.$params['name'].'" value="'.$params['value'].'" size="'.$maxlength.'" maxlength="'.$maxlength.'"/></td></tr></table>'.$script;
	return $result;
}

// cоздание элемента ввода input type="hidden"
function createHidden(&$params)
{
	if ($params['source'] == 'func')
		$params['value'] = execFunc($params['value']);
	if ($params['source'] == 'session')	
		$params['value'] = $_SESSION[$params['value']];
		
	$result = '<input type="hidden" name="'.$params['name'].'" value="'.$params['value'].'"/>';
	return $result;
}

// cоздание элемента ввода select
function createSelect(&$params, &$list)
{
	if (count($list) == 0 && trim($params['array']) !='')
	{
		$L = explode(';', $params['array']);
		for ($i=0; $i<count($L); $i++)
		{
			$p = explode('=', $L[$i]);
			$list[$p[0]] = $p[1];
		}
	}

	$result ='<table class="parameter"><tr><td class="title">'.$params['title'].'</td><td class="value">'.
	select_list($list, $params['name'], '', 0)
	.'</td></tr></table>';
	
	return $result;
}

// cоздание элемента ввода input type="checkbox"
function createCheckbox($params)
{
	return "";
}


// выполнить функцию переданую по имени
function execFunc($foo)
{
	return $foo();
}

?>