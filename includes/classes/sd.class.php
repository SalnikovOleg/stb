<?php
/*Родитель для классов Orders и Tasks модуля Service Desk*/
 abstract class SD extends Module
{
	protected $kind = 0;
	protected $state = 0;
	protected $panelName = "";
	protected $kinds_list = array();
	protected $states_list = array();
	
	public function __construct(&$db, &$params)
	{
		parent::__construct($db, $params);
		
		$this->parseFilterValues();
	}
	
	abstract function getCounts();
	abstract function getList();
	public function LoadBoxContent(){}
	
	
	public function LoadContent()
	{
		$tpl = new Template;

		$tpl->assign('CURRENT_TEMPLATE', CURRENT_TEMPLATE);
		$tpl->assign_by_ref('list', $this->getList());
		$tpl->assign('roleKind', $this->getRoleKind());
		$tpl->assign('edit_url', HOST.'ajax.php?module='.$this->Name.'&method=EditItem');
		$this->Content = $this->fetchTemplate(CURRENT_TEMPLATE."modules/sd/".$this->Params['template'], $tpl);
	}
	
	// загрузка тулбара для заказов статистика и фильтр
	public function LoadToolBar()
	{
		$tpl = new Template;
	
		$this->loadDictionarys();
		
		$tpl->assign('panelId', 'panel_'.$this->panelName);
		$tpl->assign('panel', $this->panelName);
		
		$tpl->assign('CURRENT_TEMPLATE', CURRENT_TEMPLATE);
		$tpl->assign('caption', $this->Caption);
		$tpl->assign('counts', $this->getCounts());
		$tpl->assign('refresh_url', substr(HOST,0, strlen(HOST)-1).$_SERVER['REQUEST_URI']);
		$tpl->assign('addnew_url', HOST.'ajax.php?module='.$this->Name.'&method=NewItem');
		
		$tpl->assign('kind', select_list($this->kinds_list, $this->panelName.'_kind', 'onChange="document.getElementById(\''.$this->panelName.'_list\').submit();"', $this->kind, -1) );
		$tpl->assign('state', select_list($this->states_list, $this->panelName.'_state', 'onChange="document.getElementById(\''.$this->panelName.'_list\').submit();"', $this->state, -1) );

		$this->Content = $this->fetchTemplate(CURRENT_TEMPLATE."modules/sd/tool_bar.html", $tpl);
			
		return $this->Content;	
	}
	
	protected function getRoleKind()
	{
		return $_SESSION['RoleId'] % 2 ;
	}
	
	
	// разбор переданных переменных фильтра
	protected function parseFilterValues()
	{
		if (isset($_POST[$this->panelName.'_kind']))
		{
			$this->kind = $_POST[$this->panelName.'_kind'];
			$_SESSION[$this->panelName.'_kind'] = $_POST[$this->panelName.'_kind'];
		}
		else
			$this->kind = $_SESSION[$this->panelName.'_kind'];
			
		if (isset($_POST[$this->panelName.'_state']))
		{
			$this->state = $_POST[$this->panelName.'_state'];
			$_SESSION[$this->panelName.'_state'] = $_POST[$this->panelName.'_state'];
		}
		else
			$this->state = $_SESSION[$this->panelName.'_state'];
			
	}
	
	protected function filterExpression()
	{
		$expression = "";
		if ($this->kind != 0 )
			$expression .= ' AND kind = '.$this->kind;
		if 	($this->state != 0 )
			$expression .= ' AND state = '.$this->state;
			
		return $expression;	
	}
	

	// создание и вывод списка заказов
	public function LoadList()
	{
		$this->LoadContent();
		return $this->Content;
	}

	
	// загрузка описания элемента
	public function LoadItemDescription(&$params)
	{
		$sql = "SELECT d.id, d.insert_date, d.text, u.`name` as UserName FROM ".$this->table."_description d
		INNER JOIN ".$this->table." t ON t.id = ".$params['id']." AND t.description_id = d.id
		INNER JOIN a_users u ON u.id = d.user_id
		WHERE d.deleted = 0";
		
		$ds = $this->db->db_get_array($sql);
		
		$content =$this->descriptionBuild(0, $ds);
		
		$this->getDescriptionRow($ds['id'], 1, $content);
		
		return $content;
	}
	
	private function getDescriptionRow($cId, $level, &$content)
	{
		$sql = "SELECT d.id, d.insert_date, d.text, u.`name` as UserName FROM ".$this->table."_description d
		INNER JOIN a_users u ON u.id = d.user_id WHERE d.deleted = 0 AND d.parent = ".$cId." ORDER BY d.insert_date";

		$ds = $this->db->db_dataset_array($sql);
		for($i=0; $i<count($ds); $i++)
		{
			$content .= $this->descriptionBuild($level, $ds[$i]);
			$this->getDescriptionRow($ds[$i]['id'], $level + 1, $content);
		}
		
	}
	
	// строитель коментариев
	private function descriptionBuild($level, $data)
	{
		$edit_url = "";
		return '<div class="item i'.$level.'"><div class="item_head">
		<table width="100%"><tr><td class="date">'.$data['insert_date'].'<td class="user">'.$data['UserName']."<td class='button'><a href=\"#dialog\" name=\"modal\" onClick=\"getHTMLContent('dialog', '".$edit_url."&itemId=".$data['id']."');\">Ответить</a></table></div>\n
		<div class='item_body'>".$data['text']."</div></div>\n";
	}
	
	// получить список файлов
	protected function getAttachments($id)
	{
		$sql = "SELECT `name`, `insert_date` FROM ".$this->table."_files WHERE parent =".$id." ORDER BY 2";

		return $this->db->db_dataset_array($sql);
	}
	
	// загрузка списка справочников статусов и типов 
	protected function loadDictionarys()
	{
		if (file_exists('./cache/'.$this->table.'_dict.php') != false)
			$this->readDict('./cache/'.$this->table.'_dict.php', $this->kinds_list, $this->states_list);
		else
		{
			$this->kinds_list = $this->db->db_get_list("SELECT id, `name` FROM ".$this->table."_kind");
			$this->states_list = $this->db->db_get_list("SELECT id, `name` FROM ".$this->table."_state");
			
			$this->saveDict('./cache/'.$this->table.'_dict.php', $this->kinds_list, $this->states_list);
		}
	}
	
	// сохранение списков в файл
	private function saveDict($fileName, $kinds, $states)
	{
		$fileText = "<?php \n";
		
		$fileText .= '$kinds = array(  ';
		if (count($kinds)>0)
			foreach ($kinds as $k => $v)
				$fileText .= $k." => '".$v."', ";
		$fileText = substr($fileText, 0, strlen($fileText)-2).");\n";
		
		$fileText .= '$states = array(  ';
		if (count($states) >0)
			foreach ($states as $k => $v)
				$fileText .= $k." => '".$v."', ";
		$fileText = substr($fileText, 0, strlen($fileText)-2).");\n";
		
		$fileText .="?>";
		
		write_to_file($fileName, $fileText);
	}
	
	// чтение списков из файлов
	private function readDict($fileName, &$kinds, &$states)
	{
		include $fileName;
	}
	
	
	//переопределенный метод 	класса  module
	public function NewItem(&$params)
	{
		$params['action'] = 'insert';
		$params['action_title'] = 'Создать ';
			
		return $this->EditItem($params);
	}
}
?>