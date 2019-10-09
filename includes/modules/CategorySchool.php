<?php
class CategorySchool extends Module {
	
	function __construct(&$db, &$params)
	{
		parent::__construct($db, $params);
		$this->table = "e_school_category";
	}
	
	public function LoadBoxContent(){}
	public function LoadContent(){}
	

//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++	
//                    АДМИНКА
//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	
	// ++++++++++++++++++++++++++++   редактирование 
	public function EditItem(&$params)
	{
		if (!isset($params['itemId'])) $id = 0; 
		else $id = (int)$params['itemId'];

		$category = $this->GetItem($id);
		
		includeModule('Country');
		$p = null;
		$country = new Country($this->db, $p);
		$countrys = $country->GetItems();
		
		$this->tpl->assign_by_ref('category', $category);
		$this->tpl->assign_by_ref('countrys', $countrys);
		$this->tpl->assign('select_school_url', HOST.'admin/ajax.php?module=CategorySchool&method=getSchool&category_id='.$id.'&country_id=');
		
		$this->tpl->assign('table', $this->getTable($id));
		$this->tpl->assign('action', 'update');
		$this->tpl->assign('action_title', 'Выбрать школы для категорий');
		$this->tpl->assign('module_url', HOST_ADMIN.'?module='.$params['module']);
		$this->tpl->assign('parent_module_url', HOST_ADMIN.'?module=Category&method=ItemsList');
		$this->tpl->assign('ajax_url', HOST.'admin/ajax.php?');
		$this->tpl->assign('language', $_SESSION['lang_folder']);	
		
		$this->Content = $this->fetchTemplate(CURRENT_TEMPLATE."modules/cat_school_form.html", $this->tpl);
		
		$this->Navigator .=$category['parent_name'].$category['name'];	 
	}
	
	// получить данные текущей категории
	private function GetItem($id)
	{
		$sql="SELECT c.id, cl.`name`, IFNULL(CONCAT(pcl.`name`, ' :: '), '') as parent_name
		FROM e_category c
		INNER JOIN e_category_lang AS cl ON cl.category_id = c.id AND cl.lang = ".$_SESSION['lang_id']."	
		LEFT JOIN e_category_lang AS pcl ON pcl.category_id = c.parent AND pcl.lang = ".$_SESSION['lang_id']." AND c.parent <> 0
		WHERE c.id = ".$id ;

		$item = $this->db->db_get_array($sql);

		return $item;
	}	
	
	// получить таблицу со списком школ
	public function getTable($id)
	{
		$sql = "SELECT scat.school_id, CONCAT(sl.`name`,' ', '1') as `name`, s.disabled, GROUP_CONCAT(cl.`name`) AS country
			FROM `e_school_category` scat
			INNER JOIN e_school s ON s.id = scat.school_id
			INNER JOIN e_school_lang sl ON sl.school_id = scat.school_id AND sl.lang = ".$_SESSION['lang_id']."
			INNER JOIN e_school_country scnt ON scnt.school_id = scat.school_id
			INNER JOIN e_country_lang cl ON cl.country_id = scnt.country_id AND cl.lang = ".$_SESSION['lang_id']."
			WHERE scat.category_id =".$id."
			GROUP BY scat.school_id, CONCAT(sl.`name`,' ', '1'), s.disabled";

		$tpl = new AdminTemplate;
		$tpl->assign_by_ref('list', $this->db->db_dataset_array($sql));
		$tpl->assign('language', $_SESSION['lang_folder']);
		
		$tpl->assign('del_url', HOST.'admin/ajax.php?module=CategorySchool&method=delSchool&category_id='.$id.'&school_id=');
		
		return $this->fetchTemplate("modules/cat_school_table.html", $tpl);
	}
	
	// выбор списка школ для выбранной страны
	public function getSchool(&$data)
	{
		$sql = "SELECT s.id, sl.`name` FROM e_school_lang sl 
		INNER JOIN e_school_country sc ON sl.school_id = sc.school_id 
		INNER JOIN e_school s ON s.id = sl.school_id
		WHERE  sl.lang = ".$_SESSION['lang_id']." AND sc.country_id = ".(int)$data['country_id']." AND s.disabled = 0";

		$list = $this->db->db_dataset_array($sql);
		
		$result = "<ul>";
		for ($i=0; $i<count($list); $i++)
			$result .='<li><a href="javascript:void(0);" onclick="$(\'#table\').load(\''.HOST.'admin/ajax.php?module=CategorySchool&method=addSchool&category_id='.(int)$data['category_id'].'&school_id='.$list[$i]['id'].'\');">'.$list[$i]['name'].'</a></li>';
		
		$result .= "</ul>";
		
		return $result;
	}
	
	// добавление поля
	public function addSchool(&$data)
	{
		$sql = "INSERT INTO ".$this->table." (category_id, school_id) VALUES (".$data['category_id'].", '".$data['school_id']."')";
		$this->db->db_query($sql);
		return $this->getTable($data['category_id']);
	}
	
	// удаление поля
	public function delSchool(&$data)
	{
		$sql = "DELETE FROM ".$this->table." WHERE category_id = ".$data['category_id']." AND school_id = '".$data['school_id']."'";
	
		$this->db->db_query($sql);
		return $this->getTable($data['category_id']);
	}
}
?>