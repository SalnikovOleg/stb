<?php
abstract class DB
{
	protected $link = null;
	protected $sql_array = array();
	
	// --------------------  query  ----------------------------------------
	abstract function db_query($query);

	// ---------------------  close db  ------------------------------------
	abstract function db_close();
	
	// получить строку датасета
	abstract function fetch_row($qry);
	
	// получить строку датасета в виде массива 
	abstract function fetch_array($qry);
	
	// --------------- get value ------------------------------------------
	public function db_get_value($sql)
	{
		$result=null;

		if ($qry = $this->db_query($sql))
		if ($result = $this->fetch_row($qry))
			$result = $result[0];  
		
		if (is_resource($qry))
			mysql_free_result($qry);
			
		return $result;      
	}

	// --------------- select array  --------------------------------------
	public function db_get_array($sql)
	{
		if ($qry = $this->db_query($sql))
		{
			$result = $this->fetch_array($qry);
			
			if (is_resource($qry))
				mysql_free_result($qry);
				
			if ($result)
				return $result;
			else return null;
		}
		else return null;
	}

	// --------------- select array  --------------------------------------
	public function db_get_row($sql)
	{
		if ($qry = $this->db_query($sql))
		{
			$result = $this->fetch_row($qry);
			
			if (is_resource($qry))
				mysql_free_result($qry);
				
			if ($result)
				return $result;
			else return null;
		}	
		else return null;
	}

	// ----------------------  select list  --------------------------------
	public function db_get_list($sql, $key_name='', $value_name='')
	{
		if ($qry = $this->db_query($sql))
		{ 
			if ($key_name=='')
				while ($row = $this->fetch_row($qry))
					$result[$row[0]] = $row[1];
			else	  
				while ($row = $this->fetch_array($qry))
					$result[$row[$key_name]] = $row[$value_name];
		}	
  	
		if (is_resource($qry))
			mysql_free_result($qry);
			
		if (!empty($result)) return $result; 
		else return null;
	}

	//  ----------------  select dataset array  ---------------------------
	public function db_dataset_array($sql)
	{
		if ($qry=$this->db_query($sql))
			while ($row=$this->fetch_array($qry))
				$result[]=$row;
		
		if (is_resource($qry))
			mysql_free_result($qry);
			
		if (!empty($result)) return $result;		
		else return null;
	}

	//  ----------------  select index array of array by $id_field  --------
	public function db_index_array($sql,$id_field)
	{
		if ($qry=$this->db_query($sql))
			while ($row=$this->fetch_array($qry))
				$result[$row[$id_field]]=$row;
		
		if (is_resource($qry))
			mysql_free_result($qry);		
		
		if (!empty($result)) return $result;		
		else return null;
	}

	//  --------------------  select dataset_row  -------------------------
	public function db_dataset_row($sql)
	{
		if ($qry=$this->db_query($sql))
			while ($row=$this->fetch_row($qry))
				$result[]=$row;
	
		if (is_resource($qry))
			mysql_free_result($qry);		
			
		if (!empty($result)) return $result;		
		else return null;
	}
		
	// полечить следующий id в таблице
	public function get_next_id($table, $field)
	{
		$result = $this->db_get_value('select max('.$field.') from '.$table);
		if ($result) return $result+1;
		else return 1;
	}
	
	// отобразить датасет
	public function viewDataSet(&$dataset)
	{
		foreach ($dataset as $key=>$val)
		{
			echo $key,"<br>";
			foreach ($val AS $k => $v)
				echo $k,"=>",$v," | ";
			echo "<br>";	
		}
	}
	
		// отобразить датасет
	public function viewRow(&$dataset)
	{
		foreach ($dataset as $key=>$val)
			echo $key,"=>",$val," | ";
	}
	
	public function queryLog()
	{
		$name ='./upload/query_'.date("Y-m-d_His").'.log';
		$f = fopen($name,"w");
		foreach ($this->sql_array as $row)
			fwrite($f, $row['time']."\t".$row['sql']."\n");
		
		fwrite($f, "Всего запросов\t".count($this->sql_array)."\n");	
		
		fclose($f);
	}
}
?>