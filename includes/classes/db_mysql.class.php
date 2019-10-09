<?php
class DB_MySql extends DB 
{
	public function __construct()
	{
		$this->db_connect() or die('Unable to connect to database server!'); 
	}
	
	// ----------------  connect to db  
	public function db_connect($server = DB_SERVER, $username = DB_USERNAME, $password = DB_PASSWORD, $database = DB_DATABASE) 
	{
		$this->link = @mysqli_connect($server, $username, $password);

		if ($this->link) mysqli_select_db($database);
		
		mysqli_query ('set NAMES "utf8"');
		
		return $this->link;
	}

	// --------------------  query  
	public function db_query($query) 
	{
		if (LOGGED) $time_start=explode(' ', microtime()); 

		$result = mysqli_query($query, $this->link);

		if (LOGGED) 
		{
			$time_end = explode(' ', microtime());
			$this->sql_array[]=array('sql'=>$query, 'time'=>number_format(($time_end[1] + $time_end[0] - ($time_start[1] + $time_start[0])), 3) );
		}
  
		return $result;
	}

	public function get_insert_id()
	{
		return mysqli_insert_id();
	}
	
	// получить строку датасета
	public function fetch_row($qry)
	{
		return mysqli_fetch_row($qry);
	}
	
	// получить строку датасета в виде массива 
	public function fetch_array($qry)
	{
		$array = mysqli_fetch_array($qry);
		if ($array !== false) 
		 foreach ($array as $k => $v)
			if (is_numeric($k)) 
				unset($array[$k]);
		return $array;
	}
	
	// -------  close db  
	public function db_close() 
	{
		mysqli_close($this->link);
	}

	
	// парсер запроса, получение списка названий полей
	public function getFieldsList($sql)
	{
		$result = null;
		
		$fields = substr($sql, strpos(strtolower($sql), 'select')+6, strpos(strtolower($sql), 'from')- strpos(strtolower($sql), 'select')-6 ); // получить список полей между select и from
		$fields = str_replace('`', '', $fields); // убрать кавычки `
		$fields = $this->sqlDelFunction($fields); //убрать запятые из функций
		
		// разделить список полей по запятым
		$fields = explode(',', $fields);
		foreach ($fields as $key=>$value)
		{
			$value = substr($value, strpos($value, '.')+1 ); // убрать алиасы
			
			if (strpos(strtolower($value), ' as ') > 0) // если задано имя стобца AS -выделить его
				$value = substr($value, strpos(strtolower($value), ' as ')+4);
			
			if (strpos(trim($value), ' ') > 0) // если задано имя стобца после пробела -выделить его
				$value = substr($value, strpos(trim($value), ' '));
			
			$result[] = $value;	
		}
		
		return $result;
	}	
	
	public function sqlDelFunction($sql)
	{
		preg_match("( (IFNULL|CONCAT|LEFT|RIGHT|SUBSTRING|REPLACE|DATEPART)\(.+\) )", $sql, $matches);
		foreach ($matches as $item)
		  $sql = str_replace($item, str_replace(',','',$item), $sql);
		
		return $sql;	
	}
	
	public function mysqli_free_result($result){}
}
?>