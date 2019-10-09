<?php
class Params extends Module
{
	private $cp = '';
	private $cache_file = "";
	
	function __construct(&$db, $params)
	{
		if (isset($params['prefix']))
			$this->cp = $params['prefix'];
		$this->table = "a_params";
		$this->cache_file = $this->table.'.php';
		parent::__construct($db, $params);
	}
	
	private function LoadList()	{}
	public function LoadBoxContent() {}
	public function LoadContent() {}

	public function LoadParams()
	{
		//Выбор основных параметров сайта
		if (file_exists($this->cp.'./cache/'.$this->cache_file) == false)
		{
			$sql = "SELECT `key`, `value` FROM ".T_PARAMS." WHERE `group_id` > 0";
			$p = $this->db->db_get_list($sql, 'key', 'value');
			$fileText="<?php\n";
			foreach ($p as $key =>$value)
				$fileText .= "define('".$key."', '".$value."');\n";
			$fileText.="?>";
			write_to_file($this->cp.'./cache/'.$this->cache_file, $fileText);
		}
		
		include $this->cp.'./cache/'.$this->cache_file;
	
	}
	
	public function LoadParamsForLang()
	{
		if (file_exists($this->cp.'./cache/'.$this->table.'_'.$_SESSION['lang_url'].'.php') == false)
		{
			$sql = "SELECT `key`, `value` FROM ".T_PARAMS." WHERE `group_id` = 0 AND `lang` = ".$_SESSION['lang_id'];
			$p = $this->db->db_get_list($sql, 'key', 'value');
			$fileText="<?php\n";
			foreach ($p as $key =>$value)
				$fileText .= "define('".$key."', '".$value."');\n";
			$fileText.="?>";
			write_to_file($this->cp.'./cache/'.$this->table.'_'.$_SESSION['lang_url'].'.php', $fileText);	
		}

		include $this->cp.'./cache/'.$this->table.'_'.$_SESSION['lang_url'].'.php';
	}
	
//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++		
//---------------------------   admin  ------------------------------------------
//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	public function ItemsList(&$params)
	{
		$this->module_url = HOST_ADMIN.'?module='.$params['module'];
		//$this->method_url = '&method='.$params['method'];

		$list =$this->GetItems();
		
		$this->tpl->assign_by_ref('list', $list);
		$this->tpl->assign('module_url', $this->module_url);
		$this->tpl->assign('DB_SERVER', DB_SERVER);
		$this->tpl->assign('DB_USERNAME', DB_USERNAME);
		$this->tpl->assign('DB_PASSWORD', DB_PASSWORD);
		$this->tpl->assign('DB_DATABASE', DB_DATABASE);
		
		if (file_exists("./templates/".CURRENT_TEMPLATE."modules/".$this->Params['template']) == true) 
			$this->Content = $this->tpl->fetch(CURRENT_TEMPLATE."modules/".$this->Params['template']);
		else 	
			$this->Content = "Не найден шаблон ".CURRENT_TEMPLATE."modules/".$this->Params['template'];
		
		$this->AdminNavigator("");
	}

	// получить список подкатегорий для заданного id
	private function GetItems()
	{
		$sql = "SELECT `id`, `group_id`, `key`, `value`, `caption`, `input_type`, `condition`, `ordno` 
		FROM ".$this->table." WHERE `group_id` = 0 AND `lang` = ".$_SESSION['lang_id']." AND deleted = 0
		UNION
		SELECT `id`, `group_id`, `key`, `value`, `caption`, `input_type`, `condition`, `ordno` 
		FROM ".$this->table." WHERE `group_id` > 0 AND deleted = 0
		ORDER BY `group_id`, `ordno`";

		$params = $this->db->db_dataset_array($sql);
		
		$list =array();
		
		for ($i=0; $i<count($params); $i++)
		{
			$list[$i]['caption'] = $params[$i]['caption'];	
			
			switch ($params[$i]['input_type'])
			{
				case "text":
					$list[$i]['value'] = '<input class="text" type="text" name="'.$params[$i]['key'].'" value="'.$params[$i]['value'].'">';
				break;
				
				case "checkbox":
					$list[$i]['value'] = '<input type="checkbox" name="'.$params[$i]['key'].'" '.checked($params[$i]['value']).'>';
				break;

				case "select":
					$sql = explode(":", $params[$i]['condition']);
					
					if (isset($sql[0]) && $sql[0] =="sql")
						$list[$i]['value'] = select_list($this->db->db_get_list($sql[1], 'id', 'name'), $params[$i]['key'], "", $params[$i]['value']);
						
					if (isset($sql[0]) && $sql[0] =="folder")
						$list[$i]['value'] = select_loaded_files('./../'.$sql[1].'/', $params[$i]['key'], '', $params[$i]['value'], 1, '-- выберите шаблон сайта--');
					
				break;
				
			}
		}
		
		unset($params);

		return $list;
	}
	
//------------    save data  -------------	
	public function update(&$data)
	{
		$sql = "UPDATE ".$this->table." SET `value` = '0' WHERE `key` = 'MULTILANGUAGE'" ;
		$this->db->db_query($sql);
		
		foreach ($data as $key=>$val)
		{
			if ($key == "action") continue;
			if ($val == "on") $val = 1;
			$sql ="SELECT id FROM ".$this->table." p WHERE p.`key` = '".$key."' AND p.lang = (CASE p.`group_id` WHEN 0 THEN ".$_SESSION['lang_id']." ELSE 0 END)"; 
			$id = $this->db->db_get_value($sql);
			$sql = "UPDATE ".$this->table." SET `value` = '".$val."' WHERE id = ".$id;

			$this->db->db_query($sql);
		}
		
		$this->flush_cache();
		file_del("./cache/*params*.php");
	}
	

}
?>
