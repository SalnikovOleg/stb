<?php
class Dictionary extends Module{
	
	function __construct(&$db, &$params)
	{
		parent::__construct($db, $params);
		if (isset($_GET['table']))
			$this->table = $_GET['table'];
	}
	
	public function LoadBoxContent(){}
	public function LoadContent(){}
	
	public function ItemsList(&$params)
	{
		$this->tpl->config_load($_SESSION['lang_folder']."/admin.cfg", 'dictionary');
		$vars = $this->tpl->get_config_vars();
		
		$this->tpl->assign_by_ref('caption', $vars);
		$this->tpl->assign('list', $this->getList());
		$this->tpl->assign('table', $this->table);
		$this->tpl->assign('language', $_SESSION['lang_folder']);	
		
		$this->Content = $this->fetchTemplate(CURRENT_TEMPLATE.'modules/dict_list.html', $this->tpl);
		
		$this->Navigator = $vars['dictionary']." :: ".$vars[$this->table];
	}

	private function getList()
	{
		$sql = "SELECT id, `name` FROM ".$this->table." WHERE lang = ".$_SESSION['lang_id'];

		$list = $this->db->db_get_list($sql); 
		
		$result = '<ul class="dict">';
		foreach($list as $key => $val)
			$result .='<li><input type="text" id="'.$key.'" value="'.$val.'" size="100" maxlength="255"/>
			&nbsp;&nbsp;<input type="button" value="update" onclick="updateItem('.$key.');">
			&nbsp;&nbsp;<a href="javascript:void(0);" onclick="delItem('.$key.');"><img src="./admin/templates/images/delete.gif" /></a>
			</li>';
		$result .= "</ul>";
		
		return  $result;
	}
	
	public function addItem(&$data)
	{
		$id = $this->db->db_get_value("SELECT MAX(id) FROM ".$data['table'])+1;
		$list = $this->db->db_get_list("SELECT id, id FROM a_language WHERE deleted = 0");
		$sql = "INSERT INTO ".$data['table']." (id, lang, `name`) VALUES ";
	
		foreach ($list as $lang)
			$sql .= "(".$id.", ".$lang.", '".$data['name']."'), ";
		
		$sql = substr($sql, 0, strlen($sql)-2);
		$this->db->db_query($sql);

		file_del('./../cache/*'.$data['table'].'*.*');
		file_del('./cache/*'.$data['table'].'*.*');
		
		return $this->getList();
	}

	public function updateItem(&$data)
	{
		$sql = "UPDATE ".$data['table']." SET `name` = '".$data['name']."' WHERE id = ".$data['id']." AND lang = ".$_SESSION['lang_id'];
        $this->db->db_query($sql);

		file_del('./../cache/*'.$data['table'].'*.*');
		file_del('./cache/*'.$data['table'].'*.*');

		return $this->getList();
	}
	
	public function delItem(&$data)
	{
		$sql = "DELETE FROM ".$data['table']." WHERE id = ".$data['id'];
		$this->db->db_query($sql);

		file_del('./../cache/*'.$data['table'].'*.*');
		file_del('./cache/*'.$data['table'].'*.*');
		
		return $this->getList();
	}
	
	// кешированый список
	public function loadList($table)
	{
		$file = './cache/'.$_SESSION['lang_id'].'_'.$table.'.php';

		if (file_exists($file) == false)
		{
			$sql = "SELECT id, `name` FROM ".$table." WHERE lang = ".$_SESSION['lang_id'];
			$list = $this->db->db_get_list($sql);
			$content = '<?php $list = array(';
			foreach($list as $key => $item)
				$content .= $key.'=>"'.$item.'", '."\n";
			$content =  substr($content, 0, strlen($content)-3)."\n); ?>";
			write_to_file($file, $content);
		}
		else
		{
			include $file;
		}
		
		return $list;
	}
}
?>