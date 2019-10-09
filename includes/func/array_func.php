<?php
function get_id_list_from_post(&$POST,$key_prefix,$delim)
{
	$result = null;
	foreach ($POST as $key => $value)

	if (strpos($key, $key_prefix) !== false )
	 {
	 	$id = substr($key, strpos($key,$delim)+1);
		$result[] = $id;
	 } 
	 
	return $result; 
	
}

// выбрать  значение поля $field из дата сета $dataset по заданому значеню $value  заданого поля $key
function dataset_select_value(&$dataset, $field, $key, $value, $default)
{
  $result = $default;
  for ($i=0; $i<count($dataset); $i++)	
  {
  	  if ($dataset[$i][$key] == $value)
  	  {
  	  	$result = $dataset[$i][$field];
  	  	break;
  	  }
  }
  return $result;
}

// выборка набора данных из набора данных dataset по занчению $value поля $key
function dataset_select(&$dataset, $key, $value)
{
	$new_dataset = null;
	$j=-1;
	for ($i=0; $i<count($dataset); $i++)
	{
		if ($dataset[$i][$key] == $value)
		{
			$j++;
			$new_dataset[$j] = $dataset[$i];	
		}
	}
	return $new_dataset;
}

// выборка строки из набора данных dataset по занчению $value поля $key
function dataset_select_row(&$dataset, $key, $value)
{
 $result = null;
  for ($i=0; $i<count($dataset); $i++)	
  {
  	  if ($dataset[$i][$key] == $value)
  	  {
  	  	$result = $dataset[$i];
  	  	break;
  	  }
  }
  return $result;
}


function getRandomArray($count, $limit)
{
    $result=null;
    for ($i=0; $i<$count; $i++)
    {
		do 
		{
			$x = rand(0,$limit);
		
			$find = false;
			for ($j=0; $j<count($result); $j++)
			if ($result[$j] == $x) 
			{
				$find =true;
				break;
			}
		}
		while ($find);

		$result[$i]=$x;
	}
	return $result;
}

//получение списка из набора данных по занчению поля , состоящего из ключа и поля
function get_list_from_dataset(&$dataset, $key, $value, $field)
{
	$result = null;
	foreach ($dataset as $k => $item)
		if ($item[$key] == $value)
			$result[$k] = $dataset[$k][$field];
			
	return $result;		
}

// получить список из датасета значений заданного поля
function get_list_of_field(&$dataset, $field)
{
	$result = null;
	foreach ($dataset as $key => $item)
		$result[$key] = $item[$field];
		
	return	$result;
}

// преобразовать датасет в список $field1 - индекс, $field2 - значение
function get_list(&$dataset, $field1, $field2)
{
	$result = null;
	foreach ($dataset as $item)
		$result[$item[$field1]] = $item[$field2];
		
	return	$result;
}

//получить массив уникальных значений из массива
function get_uniq_by_col(&$data, $col, $id)
{	
	$list = array();
	foreach ($data as $row)
		if (!isset($mas_hash[$row[$col]])) {
			$mas_hash[$row[$col]] = 1;
			$list[] = array($col => $row[$col], $id => $row[$id]);
		}	
	return $list;	
}
?>