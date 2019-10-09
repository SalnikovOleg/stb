<?php
class Users extends Module
{
	function __construct(&$db, $params)
	{
		parent::__construct($db, $params);
		
		$this->table = "a_users";
	}
	
	private function LoadList()	{}
	public function LoadContent() {}

	public function LoadBoxContent() 
	{
		$this->tpl->assign('UserName', $_SESSION['UserName']);
		$this->tpl->assign('RoleName', $_SESSION['RoleName']);
		return $this;
	}
	
	public function Edit(&$params)
	{
		$id = (int)$params['id'];
		$item = $this->GetItem($id);
		$tpl = new Template;
		$tpl->assign_by_ref('item', $item);
		$tpl->assign('action', 'update');
		$tpl->assign('save_url', HOST.'ajax.php?module=Users');
		
		echo $this->fetchTemplate(CURRENT_TEMPLATE.'sd/user_form.html', $tpl);
	}
	
	public function getUserId($login, $password)
	{
		return $this->db->db_get_value("SELECT id FROM a_users WHERE '".substr($login, 0, 20)."' = login AND '".md5(substr($password, 0, 20))."' = password");
	}
	
//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++		
//---------------------------   admin  ------------------------------------------
//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	public function ItemsList(&$params)
	{
		$this->module_url = HOST_ADMIN.'?module='.$params['module'];
		$this->method_url = '&method='.$params['method'];
		
		$list = $this->GetItems();
		
		for ($i=0; $i<count($list); $i++ )
			$list[$i]['url'] = $this->module_url.$this->method_url."&pId=".$list[$i]['id'];
		
		$this->tpl->assign_by_ref('list', $list);
		$this->tpl->assign('module_url', $this->module_url);
		$this->tpl->assign('method_url', $this->method_url);

		$this->tpl->assign('edit_url', '&method=EditItem');
		$this->tpl->assign('add_url', '&method=NewItem');
		$this->tpl->assign('ajax_url', HOST."admin/ajax.php?module=".$params['module']);
		
		$this->Content = $this->fetchTemplate(CURRENT_TEMPLATE."modules/".$this->Params['template'], $this->tpl);
		
		$this->AdminNavigator("");
	}

	// получить список пользователей
	private function GetItems()
	{
		$sql = "SELECT u.`id`, u.`name`, u.`login`, u.`role_id`, u.`disabled`, r.`name` AS `role`, r.`image` FROM ".$this->table." u 
		INNER JOIN a_roles AS r ON r.`id` = u.`role_id`	WHERE u.`deleted` = 0";

		return $this->db->db_dataset_array($sql);
	}

	// редактирование 
	public function EditItem(&$params)
	{
		if (!isset($params['itemId'])) $id = 0; 
		else $id = (int)$params['itemId'];
		
		if (!isset($params['action'])) 
		{
			$params['action'] = 'update';
			$params['action_title'] = 'Редактировать ';
		}

		$this->module_url = HOST_ADMIN.'?module='.$params['module'];
		$this->method_url = '&method=ItemsList';
		
		$item = $this->GetItem($id);
		
		$roles = $this->db->db_get_list("SELECT `id`, `name` FROM a_roles", 'id', 'name');
		
		$this->tpl->assign('listroles', select_list($roles, 'role_id', '', $item['role_id'], 2,'--- роль ---'));
		$this->tpl->assign('action_title', $params['action_title']);
		$this->tpl->assign('action', $params['action']);
		$this->tpl->assign_by_ref('item', $item);
		$this->tpl->assign('module_url', $this->module_url);
		$this->tpl->assign('method_url', $this->method_url);
		
		$this->Content = $this->fetchTemplate(CURRENT_TEMPLATE."modules/".$this->table."_form.html", $this->tpl);
	
		$this->AdminNavigator("");	
	}
	
	// получить данные текущей позиции
	private function GetItem($id)
	{
		if ($id != 0)
			$item = $this->db->db_get_array("SELECT u.id, u.`name`, u.login, u.password, u.role_id, u.disabled, u.email FROM ".$this->table." u WHERE u.id = ".$id);
		else
			$item = array('name'=>'', 'login'=>'', 'password'=>'', 'role_id'=>'', 'disabled'=>0, 'e-mail'=>'');

		return $item;
	}
	
	// -----------------   save data   -----------------------
	
	public function insert(&$data)
	{
		$sql = "INSERT INTO ".$this->table." (`name`, `login`, `password`, `disabled`, `role_id`, `email`)
		VALUES ('".$data['name']."', '".$data['login']."', '".md5($data['password'])."', ".bool_to_int($data,'disabled').", ".$data['role_id'].", '".$data['email']."')";
		
		$this->db->db_query($sql);
		
		return $this->db->get_insert_id();
	}
	
	public function update(&$data)
	{
		if ( isset($data['password']) && trim($data['password']) !="" ) $password= md5($data['password']);
		else $password = $data['old_password'];
		
		$sql = "UPDATE ".$this->table." SET
			`name` = '".$data['name']."',
			`login` = '".$data['login']."',
			`password` = '".$password."',
			`disabled` = ".bool_to_int($data,'disabled').",
			`role_id` = ".$data['role_id'].",
			`email` = '".$data['email']."'
			WHERE id = ".$data['id'];
		
		$this->db->db_query($sql);
	}	

}	
?>