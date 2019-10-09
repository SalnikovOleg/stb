<?php
class Boxes extends Module
{
	function __construct(&$db, $params)
	{
		parent::__construct($db, $params);
		
		$this->table = "a_boxes";
	}
	
	private function LoadList()	{}
	public function LoadBoxContent() {}
	public function LoadContent() {}

//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++		
//---------------------------   admin  ------------------------------------------
//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	public function ItemsList(&$params)
	{
		$this->module_url = HOST_ADMIN.'?module='.$params['module'];
		$this->method_url = '&method='.$params['method'];
		
		// получить список и исправить url
		$list = $this->GetItems();
	
		$this->tpl->assign_by_ref('list', $list);
		$this->tpl->assign('module_url', $this->module_url);
		$this->tpl->assign('method_url', $this->method_url);
		$this->tpl->assign('edit_url', '&method=EditItem');
		$this->tpl->assign('language', $_SESSION['lang_folder']);
		$this->tpl->assign('add_url', '&method=NewItem');
		$this->tpl->assign('ajax_url', HOST."admin/ajax.php?module=".$params['module']);
		
		$this->Content = $this->fetchTemplate(CURRENT_TEMPLATE."modules/".$this->Params['template'], $this->tpl);
		
		$this->AdminNavigator("");
	}

	// получить список подкатегорий для заданного id
	private function GetItems()
	{
		$sql = "SELECT GROUP_CONCAT(d.`name`) as documents, p.`name` AS position, b.ordno, b.`id`, btl.`name`, b.disabled, 
                mm.`name` AS method, ml.`name` as module_name
		FROM ".$this->table." b 
		INNER JOIN ".$this->table."_to_lang AS btl ON b.`id` = btl.`box_id` AND btl.lang = ".$_SESSION['lang_id']."	
		INNER JOIN ".$this->table."_to_document btd ON btd.box_id = b.id
		INNER JOIN a_documents d ON d.id = btd.doc_id
		LEFT JOIN a_positions AS p ON p.id = b.position_id
		LEFT JOIN a_modules_methods AS mm ON mm.id = b.method_id
        LEFT JOIN a_modules_to_lang ml ON ml.id = b.module_id AND ml.lang = ".$_SESSION['lang_id']."
		WHERE b.deleted = 0 AND b.template = '".MAIN_TEMPLATE."'
		GROUP BY b.`id`, btl.`name`, b.disabled, mm.`name`, p.`name`, b.ordno
		ORDER BY 1,2,3";
		
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
			$params['action_title'] = 'Редактировать блок ';
		}

		$this->module_url = HOST_ADMIN.'?module='.$params['module'];
		$this->method_url = '&method=ItemsList';
		
		$item = $this->GetItem($id);
		$positions = $this->db->get_position_list();
		$modules = $this->db->get_module_list();
		$methods = $this->db->get_method_list($item['module_id']);
		$moduleOnClick = 'onChange="getList(\'method\', \''.HOST.'admin/ajax.php?function=get_methods&id=\'+this.value); pageShow(this.value);"';

		$oFCKeditor = new FCKeditor('text');
		$oFCKeditor->BasePath = '../tools/fckeditor/';
		if (isset($item['text']))
			$oFCKeditor->Value = stripslashes($item['text']);
		$oFCKeditor->Height = 400 ;
		$editor = $oFCKeditor->Create();
		$item['text'] = $editor;
		
		$this->tpl->assign_by_ref('item', $item);
		$this->tpl->assign('pages_list', select_list($this->db->get_all_pages(), 'page_id', '', $item['page_id'], -1, '-- Новая страница --') );
		$this->tpl->assign('position', select_list($positions, 'position_id', '', $item['position_id'], -1));
		$this->tpl->assign('module', select_list($modules, 'module_id', $moduleOnClick, $item['module_id'],-1));

		if ($item['module_id'] == 0)
			$this->tpl->assign('method', select_empty('method_id', '', ''));
		else
			$this->tpl->assign('method', select_list($methods, 'method_id', '', $item['method_id']));

		$this->tpl->assign('action_title', $params['action_title']);
		$this->tpl->assign('action', $params['action']);
		$this->tpl->assign('module_url', $this->module_url);
		$this->tpl->assign('method_url', $this->method_url);

		// список модулей только для которых показывать блок
		$this->tpl->assign('formodule', select_list($modules, 'formodule_id', '', '', 0));
		$this->tpl->assign('module_list', $this->getModules($id, 'modules'));

		// список модулей для которых НЕ показывать блок
		$this->tpl->assign('except_module', select_list($modules, 'except_module_id', '', '', 0));
		$this->tpl->assign('except_module_list', $this->getModules($id, 'modulesexcept'));
		
		// список документов
		$this->tpl->assign('docs', select_list($this->db->db_get_list("SELECT id, name FROM a_documents"), 'doc_id', '', '', -1, ''));
		// список привязаных документов
		$this->tpl->assign('doc_list', $this->getDocs($id));
		
		if (file_exists("./templates/".CURRENT_TEMPLATE."modules/".$this->table."_form.html") == true) 
			$this->Content = $this->tpl->fetch(CURRENT_TEMPLATE."modules/".$this->table."_form.html");
		else 	
			$this->Content = "Не найден шаблон ".CURRENT_TEMPLATE."modules/".$this->table."_form.html";		
			
		$this->AdminNavigator("");	
		
		unset($positions, $modules, $methods);
	}

	// вывод списка документов связанных с блоком 
	private function getDocs($id)
	{
		$sql = "SELECT btd.doc_id as id, d.name FROM a_boxes_to_document btd INNER JOIN a_documents d ON d.id = btd.doc_id WHERE btd.box_id = ".$id;
		$list = $this->db->db_dataset_array($sql);
		$result = '';
		for ($i=0; $i<count($list); $i++)	
			$result .= '<li><input type="checkbox"  id="document_del-'.$list[$i]['id'].'" name="document_del-'.$list[$i]['id'].'"/> '.$list[$i]['name'];
		return $result;
	}	
	// вывод списка модулей связанных с блоком 
	private function getModules($id, $name)
	{
		$sql = "SELECT btd.module_id as id, m.title as name FROM a_boxes_to_".$name." btd INNER JOIN a_modules m ON m.id = btd.module_id WHERE btd.box_id = ".$id;
		$list = $this->db->db_dataset_array($sql);
		$result = '';
		for ($i=0; $i<count($list); $i++)	
			$result .= '<li><input type="checkbox"  id="'.$name.'_del-'.$list[$i]['id'].'" name="'.$name.'_del-'.$list[$i]['id'].'"/> '.$list[$i]['name'];
		return $result;
	}	
	
	// получить данные текущей позиции
	private function GetItem($id)
	{
		if ($id != 0)
		{
		$sql="SELECT b.id, b.position_id, b.module_id, b.disabled, b.ordno, b.formodule, b.method_id , IFNULL(btl.`name`, b.`name`) as `name`, btl.page_id, p.title, p.text, IFNULL(btm.module_id, 0) as formodule_id, btl.params
			FROM ".$this->table." b
			LEFT JOIN ".$this->table."_to_lang AS btl ON btl.box_id = b.id AND btl.lang =".$_SESSION['lang_id']."
			LEFT JOIN pages AS p ON p.id = btl.page_id
			LEFT JOIN a_boxes_to_modules btm ON btm.box_id = b.id
			WHERE b.id = ".$id ;

			$item = $this->db->db_get_array($sql);
		}
		else
			$item = array('position_id'=>0, 'module_id'=>0, 'disabled'=>0, 'ordno'=>0, 'formodule'=>0, 'method_id'=>0, 'name'=>'', 'page_id'=>0, 'doc_id'=>'1');

		return $item;
	}
	
	// -----------------   save data   -----------------------
	
	public function insert(&$data)
	{
		$sql = "INSERT INTO ".$this->table." (`position_id`, `module_id`, `disabled`, `ordno`, `formodule`, `method_id`, `name`, `template`) VALUES (".
		$data['position_id'].", ".$data['module_id'].", ".bool_to_int($data, 'disabled').", ".$data['ordno'].", ".bool_to_int($data, 'onlymain').", ".$data['method_id'].", '".$data['name']."', '".MAIN_TEMPLATE."')";

		$this->db->db_query($sql);

		$box_id = $this->db->get_insert_id();
	
		if (trim($data['text']) != "" && $data['module_id'] == 2 ) 
		{
			if ($data['page_id'] != 0)
			{	
				$page_id = $data['page_id'];
			}
			else
			{
				$sql = "INSERT INTO pages (`title`, `text`, `lang`) VALUES ('".$data['title']."', '".addslashes($data['text'])."', ".$_SESSION['lang_id'].")";

				$this->db->db_query($sql);
				$page_id = $this->db->get_insert_id();
			}	
		}
		else {
			if ($data['page_id'] != 0)
				$page_id = $data['page_id'];
			else 			
				$page_id = 'null';
		}
		
		$langs = $this->loadLangList();
		
		// если блок языков отключен
		if (count($langs) == 0)	$langs[0]['id'] = 0;
		
		$sql = "INSERT INTO ".$this->table."_to_lang (`lang`, `box_id`, `page_id`, `name`, `params`) VALUES ";
		foreach ($langs as $item)
			$sql .= "(".$item['id'].", ".$box_id.", ".$page_id.", '".$data['name']."', '".$data['params']."'),";	
		$sql = substr($sql, 0, strlen($sql)-1);

		$this->db->db_query($sql);
		
		$data['id'] = $box_id;
		
		// вставка связи блока с документом
		$this->insertBind($data, 'document', 'doc_id');
		//вставка связи блока с модулем
		$this->insertBind($data, 'modules', 'module_id');
		//вставка связи блока с except модулем 
		$this->insertBind($data, 'modulesexcept', 'module_id');		
		
		file_del('./../cache/*boxes.php');
		file_del('./cache/*boxes.php');
	}
	
	public function update(&$data)
	{
		$sql = "UPDATE ".$this->table." SET
			`module_id` = ".$data['module_id'].",
			`position_id` = ".$data['position_id'].",
			`method_id` = ".$data['method_id'].",
			`ordno` = ".$data['ordno'].",
			`disabled` = ".bool_to_int($data, 'disabled').",
			`formodule` = ".bool_to_int($data, 'formodule').",
			`template` = '".MAIN_TEMPLATE."'
			WHERE id = ".$data['id'];
;
		$this->db->db_query($sql);

		// обновление страницы если она была
		if ($data['page_id']==$data['oldpage_id'] && $data['module_id'] == 2 && $data['page_id'] !=0) 
		{
			$sql = "UPDATE pages SET title = '".$data['title']."', text = '".addslashes($data['text'])."' WHERE id = ".$data['page_id'];
			$this->db->db_query($sql);
			$page_id = $data['page_id'];
		}
		else
		{	// если модуль page  и есть текст страницы то нужно сначала вставить страницу
			if (trim($data['text']) != "" && $data['module_id'] == 2 && $data['page_id'] == 0) 
			{
				$sql = "INSERT INTO pages (`title`, `text`) VALUES ('".$data['title']."', '".addslashes($data['text'])."')";
				$this->db->db_query($sql);
				$page_id = $this->db->get_insert_id();
			}
			else if ($data['page_id'] != 0) // если страница выбрана
				$page_id = $data['page_id'];
			else	
				$page_id = 'null';
		}
		
		$sql = "UPDATE ".$this->table."_to_lang SET `name` = '".$data['name']."', page_id = ".$page_id.", params = '".$data['params']."' WHERE box_id =".$data['id']." AND lang = ".$_SESSION['lang_id'];

		$this->db->db_query($sql);

		
		// вставка связи блока с документом
		$this->insertBind($data, 'document', 'doc_id');
		//вставка связи блока с модулем
		$this->insertBind($data, 'modules', 'module_id');
		//вставка связи блока с except модулем 
		$this->insertBind($data, 'modulesexcept', 'module_id');
		
		// удаление связи с документом (типом странцы)	
		$this->delBind($data, 'document', 'doc_id');
		//удаление связи с модулями для которых показывается блок
		$this->delBind($data, 'modules', 'module_id');
		//удаление связи с модулями для которых НЕ показывается блок
		$this->delBind($data, 'modulesexcept', 'module_id');
		
		// удаление кеш файлов  
		file_del('./../cache/*boxes.php');
		file_del('./cache/*boxes.php');
	}

	// cохранение связей документов и модулей с блоком
	private function insertBind(&$data, $name, $id_name)
	{
		$list = get_id_list_from_post($data, $name.'-', '-');
		if (count($list) == 0) return;
		
		$sql = "INSERT INTO ".$this->table."_to_".$name." (box_id, ".$id_name.") VALUES ";
		foreach ($list as $id)
			if (!isset($data['del_'.$name.'-'.$id]))
				$sql .="(".(int)$data['id'].", ".$id."), ";
		
		$sql = substr($sql, 0, strlen($sql)-2);

		$this->db->db_query($sql);
	}
	
	// удаление связей блока с документами и модулями
	// $name часть имени таблицы a_boxes_to_$name
	// $id_name - имя столбца по которому удаляются данные
	private function delBind(&$data, $name, $id_name)
	{
		// удаление документа из a_box_to_documents
		$list = get_id_list_from_post($data, $name.'_del-', '-');
		if (count($list)>0){
			$list = implode(',', $list);
			$sql = "DELETE FROM ".$this->table."_to_".$name." WHERE ".$id_name." IN (".$list.") AND box_id = ".$data['id'];
			$this->db->db_query($sql);
		}
	}	

}	
?>
