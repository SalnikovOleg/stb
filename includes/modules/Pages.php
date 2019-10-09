<?php
	class Pages extends Module
	{
		function __construct(&$db, &$params)
		{
			parent::__construct($db, $params);
			$this->table = 'pages';
		}
		
		public function LoadContent()
		{
			$tpl = new Template;
			if (isset($this->Params['text']))
			{
				$tpl->assign('text', stripslashes($this->Params['text']));
				//echo stripslashes($this->Params['text']);
				$tpl->assign('title', $this->Params['caption']);
			}

			$tpl->assign('HOST', HOST);
			$tpl->assign('language', $_SESSION['lang_folder']);
			$tpl->assign('url', $_SERVER['REQUEST_URI']);
			
			if (file_exists("./templates/".CURRENT_TEMPLATE."modules/".$this->Params['template']) == true) 
				$this->Content = $tpl->fetch(CURRENT_TEMPLATE."modules/".$this->Params['template']);
			else 	
				$this->Content = "Не найден шаблон ./templates/".CURRENT_TEMPLATE."modules/".$this->Params['template'];
			
			unset ($this->tpl);
		}
		
		public function LoadBoxContent() 
		{
			if (isset($this->Params['text']))
				$this->tpl->assign('text', stripslashes($this->Params['text']));
			if (isset($this->Params['title']))
				$this->tpl->assign('title', $this->Params['caption']);
				
			$this->tpl->assign('HOST', HOST);
			$this->tpl->assign('language', $_SESSION['lang_folder']);
			$this->tpl->assign('url', $_SERVER['REQUEST_URI']);		
		}

//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++		
//---------------------------   admin  ------------------------------------------
//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	public function ItemsList(&$params)
	{
		$this->module_url = HOST_ADMIN.'?module='.$params['module'];
		$this->method_url = '&method='.$params['method'];
		
		$this->Params['template'] = 'pages_list.html';
		
		// получить список и исправить url
		$list = $this->GetItems();
		
		$this->tpl->assign_by_ref('list', $list);
		$this->tpl->assign('module_url', $this->module_url);
		$this->tpl->assign('method_url', $this->method_url);
		$this->tpl->assign('edit_url', '&method=EditItem');
		$this->tpl->assign('add_url', '&method=NewItem');
		$this->tpl->assign('ajax_url', HOST."admin/ajax.php?module=".$params['module']);
		
		if (file_exists("./templates/".CURRENT_TEMPLATE."modules/".$this->Params['template']) == true) 
			$this->Content = $this->tpl->fetch(CURRENT_TEMPLATE."modules/".$this->Params['template']);
		else 	
			$this->Content = "Не найден шаблон ".CURRENT_TEMPLATE."modules/".$this->Params['template'];
		
		$this->AdminNavigator();
	}

	// получить список подкатегорий для заданного id
	private function GetItems()
	{
		$sql = "SELECT `id`, `title`, `meta_title`, `meta_keywords`, `meta_description`, `text`, `description` FROM ".$this->table." 
		WHERE `lang` = ".$_SESSION['lang_id'];
	
		return $this->db->db_dataset_array($sql);
	}

	// редактирование 
	public function EditItem(&$params)
	{
		if (!isset($params['itemId'])) $id = -1; 
		else $id = (int)$params['itemId'];
		
		if (!isset($params['action'])) 
		{
			$params['action'] = 'update';
			$params['action_title'] = 'Изменить ';
		}

		$this->module_url = HOST_ADMIN.'?module='.$params['module'];
		$this->method_url = '&method=ItemsList';
		
		$item = $this->GetItem($id);
/*
		$oFCKeditor = new FCKeditor('text');
		$oFCKeditor->BasePath = '../tools/fckeditor/';
		$oFCKeditor->Value = stripslashes($item['text']);
		$oFCKeditor->Height = 400 ;
		$editor = $oFCKeditor->Create();
*/
		$item['text'] = "<textarea name='text' id='text' rows='40' cols='100'>".stripslashes($item['text'])."</textarea>";
		
		$this->tpl->assign_by_ref('item', $item);
		$this->tpl->assign('action_title', $params['action_title']);
		$this->tpl->assign('action', $params['action']);
		$this->tpl->assign('module_url', $this->module_url);
		$this->tpl->assign('method_url', $this->method_url);
		
		if (file_exists("./templates/".CURRENT_TEMPLATE."modules/".$this->table."_form.html") == true) 
			$this->Content = $this->tpl->fetch(CURRENT_TEMPLATE."modules/".$this->table."_form.html");
		else 	
			$this->Content = "Не найден шаблон ".CURRENT_TEMPLATE."modules/".$this->table."_form.html";		
			
		$this->AdminNavigator("");	

	}
	
	// получить данные текущей позиции
	private function GetItem($id)
	{
		if ($id != -1)
		{
			$sql="SELECT `id`, `title`, `meta_title`, `meta_description`, `meta_keywords`, `text`, `description` 
			FROM ".$this->table." WHERE `id` = ".$id;

			$item = $this->db->db_get_array($sql);
		}
		else
			$item = array('title'=>'', 'meta_keywords'=>'', 'meta_title'=>'', 'meta_description'=>'', 'text'=>'', 'description'=>'' );

		return $item;
	}
	
	// -----------------   save data   -----------------------
	
	public function insert(&$data)
	{
		$sql = "INSERT INTO pages (`title`, `text`, `meta_title`, `meta_keywords`, `meta_description`, `description`, `lang`) VALUES(
		'".$data['title']."', '".addslashes($data['text'])."', '".$data['meta_title']."', '".$data['meta_keywords']."', 
		'".$data['meta_description']."', '".addslashes($data['description'])."', ".$_SESSION['lang_id'].")";

		$this->db->db_query($sql);
	}
	
	public function update(&$data)
	{
		$sql = "UPDATE ".$this->table." SET
			`title` = '".$data['title']."',
			`text` = '".addslashes($data['text'])."',
			`meta_title` = '".$data['meta_title']."',
			`meta_keywords` = '".$data['meta_keywords']."',
			`meta_description` = '".$data['meta_description']."',
			`description` = '".addslashes($data['description'])."'
			WHERE id = ".$data['id'];

		$this->db->db_query($sql);
	}
	
}
?>
