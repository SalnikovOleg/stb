<?php
class Gallery extends Module{

	public $path_folder = '../upload/image/';
	public $folder = 'gallery/';
	public $imgFolder = 'icon_gallery/'; // папка для иконок галерей
	
	function __construct(&$db, &$params)
	{
		parent::__construct($db, $params);
		
		if ($this->realModuleUrl == '') $this->realModuleUrl = 'gallery';
		
		$this->table = "e_gallery";
		$this->cache_file = "gallery.html";
		
		$this->value = $this->query[$this->last];
	}
	
	// загрузка выделенных изображений
	public function LoadBoxContent()
	{
		$sql = "SELECT CONCAT(g.parent_url, g.url, '/') as folder, gi.`name` as image, CASE WHEN gil.alt ='' THEN gil.description ELSE gil.alt END as alt, gil.description, gil.href 
		FROM ".$this->table." g 
		INNER JOIN e_gallery_items gi ON gi.parent = g.id AND gi.disabled = 0
		LEFT JOIN e_gallery_items_lang gil ON gil.item_id = gi.id AND gil.lang = ".$_SESSION['lang_id']."
		WHERE  g.disabled = 0 AND gi.selected = 1";

		$list = $this->db->db_dataset_array($sql);
		
		$this->tpl->assign('language', $_SESSION['lang_folder']);
		$this->tpl->assign('see_more', langUrl($_SESSION['lang_url']).$this->realModuleUrl.'/');
		$this->tpl->assign('folder', $this->path_folder);
		$this->tpl->assign_by_ref('gallery', $list);
		$this->tpl->assign('cols', 3);
		$this->tpl->assign('total', count($list));
		
		return $this;
	}
	
	// кталог галерей или галерея
	public function LoadContent()
	{
		$gallery = $this->db->db_get_array("SELECT g.id, gl.`name` FROM ".$this->table." g 
		INNER JOIN ".$this->table."_lang  gl ON gl.gallery_id = g.id AND gl.lang = ".$_SESSION['lang_id']."
		WHERE g.url = '".$this->value."'");
		
		if ($gallery == null ) {
			$gallery['id'] = 0;
			$preUrl = "";
		}
		else 
			$preUrl = $this->value.'/';
		
		$this->CreateContentTree($gallery['id'], $preUrl);
		
		$this->tpl->assign_by_ref('folders', $this->tree);
		$this->tpl->assign('title', $this->Caption.'. '.$gallery['name']);
		
		$list = $this->loadImages($this->value);
		$this->tpl->assign_by_ref('list', $list);
		
		$this->tpl->assign('cols', 4);
		$this->tpl->assign('folder', $this->path_folder);
		$this->tpl->assign('language', $_SESSION['lang_folder']);
		
		$this->GetNavigator();
	
		$this->Content = $this->fetchTemplate(CURRENT_TEMPLATE."modules/".$this->Params['template'], $this->tpl);		
	}
	
	// загрузить изображения галереи
	public function loadImages($value)
	{
		if (is_numeric($value))
			$where = "g.id = ".$value;
		else
			$where = "g.url = '".$value."'";			
		
		$sql = "SELECT CONCAT(g.parent_url, g.url, '/') as folder, gi.`name` as image, gil.alt, gil.description, gil.href 
		FROM ".$this->table." g 
		INNER JOIN e_gallery_items gi ON gi.parent = g.id AND gi.disabled = 0
		LEFT JOIN e_gallery_items_lang gil ON gil.item_id = gi.id AND gil.lang = ".$_SESSION['lang_id']."
		WHERE  g.disabled = 0 AND ".$where;

		$list = $this->db->db_dataset_array($sql);
		
		return $list;
	}
	

	// создание дерева всех галерей для основной страницы галерей
	public function CreateContentTree($id, $url)
	{
		create_dir('./cache/gallery/');
		$file = './cache/gallery/'.$id.'_'.$_SESSION['lang_url'].'_'.$this->cache_file;
		if (file_exists($file) != false)
			$this->tree = file_get_contents($file);
		else
		{
			$this->tree .= "<ul class=\"gallery\">\n";
			$this->GetContentNode($id, 0, $url);
			$this->tree .= "</ul>\n";
			write_to_file($file, $this->tree);
		}			
	}		
		
	// получить ветку дерева для вывода контента
	protected function GetContentNode($cId, $level, $preUrl)
	{
		$sql = "SELECT n.`id`, n.`parent`, n.`node`, n.`url`, n.image, nl.`alt`, nl.`name` FROM ".$this->table." n
			LEFT JOIN ".$this->table."_lang nl ON nl.gallery_id = n.id AND nl.`lang`=".$_SESSION['lang_id']."
			WHERE n.`parent` = ".$cId." AND n.`disabled`=0 ORDER BY nl.`name`";

		$ds = $this->db->db_dataset_array($sql);
	
		for ($i=0; $i<count($ds); $i++)
		{
			$url = $this->createUrl($ds[$i]['url'], 0, $preUrl);
				
			if ($ds[$i]['node'] == 1)
			{
				$this->tree .= '<li><a class="node" href="'.$url.'" title="'.$ds[$i]['name'].'">'.$ds[$i]['name']."</a><br/>";
                                  //  if (file_exists($this->path_folder.$this->imgFolder.$ds[$i]['image']))
					$this->tree .='<a class="node" href="'.$url.'" title="'.$ds[$i]['name'].'"><img src="'.$this->path_folder.$this->imgFolder.$ds[$i]['image'].'" height="60" /></a>'."\n";				
				$this->tree .="\n<ul>\n";
				$this->GetContentNode($ds[$i]['id'], $level + 1, $preUrl.$ds[$i]['url'].'/');
				$this->tree .="</ul>\n";
			}
			else {
				$this->tree .= '<li><a href="'.$url.'" title="'.$ds[$i]['name'].'">'.$ds[$i]['name']."</a><br/>";

                                   //if (file_exists($this->path_folder.$this->imgFolder.$ds[$i]['image']))
				          $this->tree .='<a class="node" href="'.$url.'" title="'.$ds[$i]['name'].'"><img src="'.$this->path_folder.$this->imgFolder.$ds[$i]['image'].'" height="60" /></a>'."\n";				
                           }
		}
	}		
		
	// навигатор положения на сайте
	public function GetNavigator()
	{
		parent::GetNavigator();
			
		$sql ="";
			
		for ($i=MODULEINDEX; $i<count($this->query)-1; $i++)
			if ($i == (count($this->query)-2) )
				$sql .= "SELECT ".$i." AS id, gl.name, g.url FROM ".$this->table." g INNER JOIN ".$this->table."_lang gl ON gl.gallery_id = g.id  WHERE g.url = '".$this->query[$i]."' AND gl.lang=".$_SESSION['lang_id']." ORDER BY 1";
			else
				$sql .= "SELECT ".$i." AS id, gl.name, g.url FROM ".$this->table." g INNER JOIN ".$this->table."_lang gl ON gl.gallery_id = g.id WHERE g.url = '".$this->query[$i]."' AND gl.lang=".$_SESSION['lang_id']." UNION ";

		$ds = $this->db->db_dataset_array($sql);
			
		$url = HOST.langUrl($_SESSION['lang_url']).$this->module_url.'/';
		for ($i=0; $i<count($ds); $i++)
		{
			$url .= trim($ds[$i]['url']).'/';
			$this->Navigator .= ' :: <a href = "'.$url.'">'.$ds[$i]['name'].'</a>';
		}
	}
	
//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++		
//---------------------------   admin  ----------------------------
//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	public function ItemsList(&$params)
	{
		if (!isset($params['pId'])) $id = 0; 
		else $id = (int)$params['pId'];
		
		$this->module_url = HOST_ADMIN.'?module='.$params['module'];
		$this->method_url = '&method='.$params['method'];
		
		// получить список и исправить url
		$list = $this->GetItems($id);
		
		for ($i=0; $i<count($list); $i++ )
			$list[$i]['url'] = $this->module_url.$this->method_url."&pId=".$list[$i]['id'];
		
		$this->tpl->assign_by_ref('list', $list);
		$this->tpl->assign('module_url', $this->module_url);
		$this->tpl->assign('method_url', $this->method_url);
		$this->tpl->assign('parent_url', '&pId='.$id);
		$this->tpl->assign('edit_url', '&method=EditItem&pId='.$id);
		$this->tpl->assign('add_url', '&method=NewItem&pId='.$id);
		$this->tpl->assign('ajax_url', HOST."admin/ajax.php?module=".$params['module']);
		$this->tpl->assign('path_folder', $this->path_folder);
		$this->tpl->assign('selected_action', select_list($this->selected_action, 'action', 'onchange="select_action(this.value, \''.HOST.'admin/ajax.php?module='.$params['module'].'&method=getSelectCategories\');"','',0));		
		
		$this->Content = $this->fetchTemplate(CURRENT_TEMPLATE."modules/gallery_list.html", $this->tpl);
		$this->AdminNavigator($id);
	}

	// получить список подкатегорий для заданного id
	public function GetItems($id)
	{
		$sql = "SELECT g.`id`, g.`parent`, g.`url` as path, gl.`name`, 0 as `type`, g.`disabled`, 0 as `selected`, g.insert_date 
			FROM ".$this->table." g 
			INNER JOIN ".$this->table."_lang gl ON gl.gallery_id = g.id AND gl.`lang`=".$_SESSION['lang_id']."
			WHERE g.`parent` = ".$id." 
			UNION
			SELECT i.id, i.parent, CONCAT(g.parent_url, g.url, '/') as path, i.`name`, 1 as `type`, i.`disabled`, i.selected, i.insert_date
			FROM ".$this->table."_items i
			INNER JOIN ".$this->table." g ON g.id = i.parent
			WHERE i.parent = ".$id."
			ORDER BY 5, 6, 4";

		return $this->db->db_dataset_array($sql);
	}
		
	// получить навигатор позиции 
	protected function AdminNavigator($id)
	{
		parent::AdminNavigator("");
		
		$href = array();
		
		$this->GetParent($id, $href);
		
		$href = array_reverse($href);
		
		foreach ($href as $item)
			$this->Navigator .= ' :: '.$item;

	}
	
	// дерево разделов для навигатора
	private function GetParent($parent, &$href)
	{
		$item = $this->db->db_get_array("SELECT g.`id`, g.`parent`, gl.`name` FROM ".$this->table." g INNER JOIN ".$this->table."_lang gl ON gl.gallery_id = g.id AND gl.lang = ".$_SESSION['lang_id']." WHERE g.id = ".$parent);
		$href[] = '<a href="'.$this->module_url.$this->method_url.'&pId='.$item['id'].'">'.$item['name'].'</a>';
		if ($item['parent'] != 0 )
			$this->GetParent($item['parent'], $href);
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
		
		if ($params['type'] == 'folder')
			$item = $this->GetFolderItem($id);
		if ($params['type'] == 'file')	{
			$item = $this->GetFileItem($id);
			if ( !isset($item['url']) ) $item['url'] = $this->db->db_get_value("SELECT CONCAT(parent_url, url, '/') as old FROM ".$this->table." WHERE id = ".$params['pId']);					
		}	
			
		if ( !isset($item['parent']) ) $item['parent'] = $params['pId'];

		$this->tpl->assign_by_ref('item', $item);
		$this->tpl->assign('action_title', $params['action_title']);
		$this->tpl->assign('action', $params['action']);
		$this->tpl->assign('module_url', $this->module_url);
		$this->tpl->assign('method_url', $this->method_url);
		$this->tpl->assign('parent_url', '&pId='.$params['pId']);
		$this->tpl->assign('type', $params['type']);
	
		$this->tpl->assign('icon_folder', $this->path_folder.$this->imgFolder);
		// список изображений
		$this->tpl->assign('listimage', select_value_list(get_file_list($this->path_folder.$this->imgFolder), 'listimage', '', $item['image'], 0, '-- выберите из загруженных --'));
		
		$this->tpl->assign('HOST', HOST);
		$this->tpl->assign('language', $_SESSION['lang_folder']);
		$this->tpl->assign('path_folder', $this->path_folder);
		$this->tpl->assign('pre_url', $this->realModuleUrl.'/'.$this->getParentUrl($params['pId']));
		
		$this->Content = $this->fetchTemplate(CURRENT_TEMPLATE."modules/".$this->table."_form.html", $this->tpl);
			
		$this->AdminNavigator($params['pId']);	
	}
	
	// получить данные текущей папки
	private function GetFolderItem($id)
	{
		if ($id != 0)
		{
			$sql = "SELECT g.id, g.parent, g.url, g.disabled, g.node, g.image, gl.alt, gl.`name`, g.insert_date	FROM ".$this->table." g
			INNER JOIN ".$this->table."_lang AS gl ON gl.gallery_id = g.id AND gl.lang = ".$_SESSION['lang_id']."
			WHERE g.id = ".$id;

			$item = $this->db->db_get_array($sql);
		}
		return $item;
	}

	// получить данные текущго файла
	private function GetFileItem($id)
	{
		if ($id != 0)
		{
			$sql = "SELECT g.id, g.parent, g.disabled, g.`name`, g.insert_date, g.selected, gl.alt, gl.description, 
			CONCAT(p.parent_url, p.url, '/') as url, gl.href
			FROM ".$this->table."_items g
			INNER JOIN ".$this->table." p ON p.id = g.parent
			LEFT JOIN ".$this->table."_items_lang AS gl ON gl.item_id = g.id AND gl.lang = ".$_SESSION['lang_id']."
			WHERE g.id = ".$id;
			$item = $this->db->db_get_array($sql);
		}
		return $item;
	}
	
	// получить урл адрес всей цепочки дерева до заданной страницы
	protected function getParentUrl($id)
	{
		$sql = "SELECT CONCAT(REPLACE(IFNULL(CONCAT(n5.url,'/'),''),' ',''), REPLACE(IFNULL(CONCAT(n4.url,'/'),''),' ',''), 
			REPLACE(IFNULL(CONCAT(n3.url,'/'),''),' ',''), REPLACE(IFNULL(CONCAT(n2.url,'/'),''),' ',''), 
			REPLACE(IFNULL(CONCAT(n1.url,'/'),''),' ','')) AS `url`
		FROM ".$this->table." n1 
		LEFT JOIN ".$this->table." n2 ON n2.id = n1.parent	
		LEFT JOIN ".$this->table." n3 ON n3.id = n2.parent	
		LEFT JOIN ".$this->table." n4 ON n4.id = n3.parent
		LEFT JOIN ".$this->table." n5 ON n5.id = n4.parent
		WHERE n1.id = ".$id;

		return $this->db->db_get_value($sql);
	}	
	
//--------------------------- save  data  ---------------------------------
	// add
	public function insert(&$data)
	{
		if ($data['type'] == 'folder')
			$this->insert_folder($data);
			
		if ($data['type'] == 'file')
			$this->insert_file($data);	
			
		$this->flush_cache();
		folderDelete('../cache/gallery');
	}

	//insert file
	private function insert_file(&$data)
	{
		// загрузка файлов
		$convItem = new ImgConvert('resize', 800, 600 );
		$convThumbs = new ImgConvert('resize', 150, 150 );
		
		if ($_FILES['image']['name'] != '')
		{
			create_dir($this->path_folder.$data['oldurl'].'images/');
			create_dir($this->path_folder.$data['oldurl'].'thumbs/');
		
			$fileName = upload_file($_FILES, $this->path_folder.'temp/', 'image');
			$convItem->convert($this->path_folder.'temp/'.$fileName);
			$convItem->saveImage($this->path_folder.$data['oldurl'].'images/'.$fileName);
			$convThumbs->convert($this->path_folder.'temp/'.$fileName);
			$convThumbs->saveImage($this->path_folder.$data['oldurl'].'thumbs/'.$fileName);
			unlink($this->path_folder.'temp/'.$fileName);
		}
		else return;
		
		$sql = "INSERT INTO ".$this->table."_items (parent, name, selected, insert_date, disabled) 
		VALUES (".$data['parent'].", '".$fileName."', ".bool_to_int($data, 'selected').", '".date("Y-m-d")."', ".bool_to_int($data, 'disabled').")";
		$this->db->db_query($sql);
	
		$id = $this->db->get_insert_id();
		
		// вставка для всех языков
		$list = $this->db->db_get_list("SELECT id, id FROM a_language WHERE deleted = 0");

		$sql = "INSERT INTO ".$this->table."_items_lang (item_id, lang, alt, description, href) VALUES ";
		foreach ($list as $lang)
			$sql .= "(".$id.", ".$lang.", '".$data['alt']."', '".addslashes($data['description'])."', '".$data['href']."'), ";
		$sql = substr($sql, 0, strlen($sql)-2);
	
		$this->db->db_query($sql);
	}
	
	// insert folder
	private function insert_folder(&$data)
	{
		// загрузка иконки
		$img = $this->loadImage($data, 150, 150);
	
		if (trim($data['url']) == '')
			$url = createUrl(utf2str($data['name'], "w"), 0, '');
		else	
			$url = createUrl($data['url'], 0, '');
		
		// получить родительский url
		$parent_url = $this->realModuleUrl.'/'.$this->getParentUrl($data['parent']);
	
		if ( !create_dir($this->path_folder.$parent_url.$url)) return;
		
		//основная запись 
		$sql = "INSERT INTO ".$this->table." (`parent`, `url`, `parent_url`, `insert_date`, `disabled`, `image`) VALUES("
			.$data['parent'].", '".$url."',	'".$parent_url."', '".date("Y-m-d")."', ".bool_to_int($data, 'disabled').", '".$img."')";

		$this->db->db_query($sql);
		
		$id = $this->db->get_insert_id();

		// пометить родительнский раздел как имеющего потомков (node = 1)
		$sql = "UPDATE ".$this->table." SET node = 1 WHERE id = ".$data['parent'];
		$this->db->db_query($sql);
		
		// вставка для всех языков
		$list = $this->db->db_get_list("SELECT id, id FROM a_language WHERE deleted = 0");
		
		$sql = "INSERT INTO ".$this->table."_lang (gallery_id, lang, `name`, `alt`) VALUES ";
		foreach ($list as $lang)
			$sql .= "(".$id.", ".$lang.", '".$data['name']."', '".$data['alt']."'), ";
		$sql = substr($sql, 0, strlen($sql)-2);

		$this->db->db_query($sql);
	}
	
	//+++++++++++++++++++++++++++++++++++++++++++++++++++++++
	// update
	public function update(&$data)
	{
		if ($data['type'] == 'folder')
			$this->update_folder($data);
			
		if ($data['type'] == 'file')
			$this->update_file($data);
			
		 folderDelete('../cache/gallery');
	}

	// обновление данных изображения 
	private function update_file(&$data)
	{
		// удалить если запрошено удаление
		if (isset($data['delete_image'])){
			unlink($this->path_folder.$data['oldurl'].'images/'.$data['name']);
			unlink($this->path_folder.$data['oldurl'].'thumbs/'.$data['name']);
		}

		// загрузка файлов		
		$convItem = new ImgConvert('resize', 800, 600 );
		$convThumbs = new ImgConvert('resize', 150, 150 );
		
		if ($_FILES['image']['name'] != '')
		{
			$fileName = upload_file($_FILES, $this->path_folder.'temp/', 'image');
			$convItem->convert($this->path_folder.'temp/'.$fileName);
			$convItem->saveImage($this->path_folder.$data['oldurl'].'images/'.$fileName);
			$convThumbs->convert($this->path_folder.'temp/'.$fileName);
			$convThumbs->saveImage($this->path_folder.$data['oldurl'].'thumbs/'.$fileName);
			unlink($this->path_folder.'temp/'.$fileName);
			$fileName = "`name` = '".$fileName."',";
		}
		else 
			$fileName = "";
			
		$sql = "UPDATE ".$this->table."_items SET 
			".$fileName."
			`selected` = ".bool_to_int($data, 'selected').",
			`disabled` = ".bool_to_int($data, 'disabled')."
			WHERE id = ".$data['id'];
		$this->db->db_query($sql);
		
		$sql = "UPDATE ".$this->table."_items_lang SET 
			alt = '".$data['alt']."',
			description = '".addslashes($data['description'])."',
			href = '".$data['href']."'
		WHERE item_id = ".$data['id']." AND lang = ".$_SESSION['lang_id'];
		$this->db->db_query($sql);
	}
	
	// обновление данных папки
	private function update_folder(&$data)
	{
		// загрузка иконки
		$img = $this->loadImage($data, 150, 150);	
	
		if (trim($data['url']) == '')
			$url = createUrl(utf2str($data['name'], "w"), 0, '');
		else	
			$url = createUrl($data['url'], 0, '');

		//определить цепочку родительских url
		$parent_url = $this->realModuleUrl.'/'.$this->getParentUrl($data['parent']);
		
		$sql = "UPDATE ".$this->table." SET
			`url` = '".$url."',
			`parent_url` = '".$parent_url."',
			`disabled` = ".bool_to_int($data, 'disabled').",
			`image` = '".$img."'
		WHERE id = ".$data['id'];
		$this->db->db_query($sql);

		$sql = "UPDATE ".$this->table."_lang SET 
			`name` = '".$data['name']."',
			`alt` = '".$data['alt']."'
		WHERE gallery_id = ".$data['id']." AND lang = ".$_SESSION['lang_id'];
		$this->db->db_query($sql);
		
		if (trim($data['oldurl']) != trim($data['url']))
			rename($this->path_folder.$parent_url.$data['oldurl'], $this->path_folder.$parent_url.$data['url']);
			
		//изменить родительские урл для всех подчиненных разделов и статей
		if ($data['node'] == 1)
			$this->updateChieldsParentUrl($data['id'], $parent_url.$data['url'].'/');
	}
	
	
// изменить родительские урл для всех подчиненных $id разделов и статей
	private function updateChieldsParentUrl($id, $url)
	{
		// выбираем всех потомков заданной категории
		$sql = "SELECT id, url, node FROM ".$this->table." WHERE parent = ".$id;
		$list = $this->db->db_dataset_array($sql);
		for ($i=0; $i<count($list); $i++) {
			// обновляем parent_url потомков заданным $url
			$sql = "UPDATE ".$this->table." SET parent_url = '".$url."' WHERE id = ".$list[$i]['id'];
			$this->db->db_query($sql);
			
			if ($list[$i]['node'] == 1) // если у потомка есть свои потомки то вызываем процедуру для них
				$this->updateChieldsParentUrl($list[$i]['id'], $url.$list[$i]['url'].'/');
		}
	}	
	
	//+++++++++++++++++++++++++++++++++++++++++++++++++++++++	
	//          Delete
	//+++++++++++++++++++++++++++++++++++++++++++++++++++++++	
	public function delFolder(&$data)
	{
		$sql = "SELECT CONCAT(g.parent_url, g.url) FROM  ".$this->table." g WHERE g.id = ".$data['id'];
		$folder = $this->db->db_get_value($sql);
		//рекурсивное удаление всех папок и файлов
		folderDelete($this->path_folder.$folder);
		
		// получить список всех id шников вложеных папок
		$id_list = $data['id'];
		$this->getChieldId($data['id'], $id_list);

		//удалить записи из бд
		$sql = "DELETE FROM ".$this->table." WHERE id IN (".$id_list.")";
		$this->db->db_query($sql);
		$sql = "DELETE FROM ".$this->table."_lang WHERE gallery_id IN (".$id_list.")";
		$this->db->db_query($sql);
		$sql = "DELETE FROM ".$this->table."_items WHERE parent IN (".$id_list.")";
		$this->db->db_query($sql);
		$sql = "DELETE FROM ".$this->table."_items_lang WHERE parent IN (".$id_list.")";
		$this->db->db_query($sql);

 folderDelete('../cache/gallery');
	}
	
	// получить список id всех подчиненных папок
	private function getChieldId($id, &$result)
	{
		$list = $this->db->db_get_list("SELECT id, id FROM ".$this->table." WHERE parent = ".$id);
		if (count($list > 0))
			foreach ($list as $k){
				$result .=','.$k;
				$this->getChieldId($k, $result);
			}	
	}
	
	// удалить файл
	public function delFile(&$data)
	{
		$sql = "SELECT i.`name`, CONCAT(g.parent_url, g.url, '/') as url FROM ".$this->table."_items i INNER JOIN ".$this->table." g ON g.id = i.parent WHERE i.id = ".$data['id'];
echo $sql;
		$file = $this->db->db_get_array($sql);

		unlink($this->path_folder.$file['url'].'images/'.$file['name']);
		unlink($this->path_folder.$file['url'].'thumbs/'.$file['name']);
		
		$sql = "DELETE FROM ".$this->table."_items WHERE id = ".$data['id'];
		$this->db->db_query($sql);
		
		$sql = "DELETE FROM ".$this->table."_items_lang WHERE item_id = ".$data['id'];
		$this->db->db_query($sql);
 folderDelete('../cache/gallery');
	}
	
//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
// массовые действия 
//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

// удаление выбраных папок или файлов
	public function deleting(&$data)
	{
		// удаление каталогов
		$cat_id = get_id_list_from_post($data,'chk_cat-','-');
		if ($cat_id != null) 
			foreach ($cat_id as $id){
				$data['id'] = $id;
				$this->delFolder($data);
			}
			
		// удаление файлов	
		$file_id = get_id_list_from_post($data, 'chk_file-', '-');
		if ($file_id != null) 
			foreach ($file_id as $id){
				$data['id'] = $id;
				$this->delFile($data);
			}
		
		$this->flush_cache();
 folderDelete('../cache/gallery');
	}

	
// переключение включения отключения категорий
	public function enable(&$data)
	{
		$this->disable_switch($data, 0);
	}
	
	public function disable(&$data)
	{
		$this->disable_switch($data, 1);
	}

	private function disable_switch(&$data, $disable)
	{
		$cat_id=get_id_list_from_post($data, 'chk_cat-','-');
		if ($cat_id != null) 
		{
			$cat_id = implode(',',$cat_id);
			$sql = 'UPDATE '.$this->table.' set `disabled`= '.$disable.' WHERE `id` in ('.$cat_id.')';
			$this->db->db_query($sql);  
		}	
		
		$cat_id=get_id_list_from_post($data, 'chk_file-', '-');
		if ($cat_id != null) 
		{
			$cat_id = implode(',',$cat_id);
			$sql = 'UPDATE '.$this->table.'_items set `disabled`= '.$disable.' WHERE `id` in ('.$cat_id.')';
			$this->db->db_query($sql);  
		}	
		
		$this->flush_cache();
 folderDelete('../cache/gallery');
	}
	
// перемещение выбраных папок или файлов сразу
	public function moving(&$data)
	{
		$this->moveFolder($data);
		
		$this->moveFile($data);
		
		$this->flush_cache();
 folderDelete('../cache/gallery');
	}

	
	// перемещение папок
	private function moveFolder(&$data)
	{
		$cat_id=get_id_list_from_post($data,'chk_cat-','-');
		
		if ($cat_id == null) return;

		// физическое перемещение папки

		// изменение в БД
		$cats = implode(',',$cat_id);
		$sql = 'UPDATE '.$this->table.' SET `parent`='.$data['gallery_id'].' WHERE `id` in ('.$cats.')';
		$this->db->db_query($sql);  
			
		// пометить папку как узловую 
		$sql = 'UPDATE '.$this->table.' SET node = 1 WHERE  id = '.$data['gallery_id'];
		$this->db->db_query($sql);  
			
		// обновить парентты для всех вложеных папок
		$parent_url = $this->realModuleUrl.'/'.$this->getParentUrl($data['gallery_id']);
		$this->updateChieldsParentUrl($data['gallery_id'], $parent_url);
	}
	
	
	// перемещение файлов	
	private function moveFile(&$data)
	{
		$cat_id=get_id_list_from_post($data,'chk_file-','-');
	
		if ($cat_id == null) return;

		$cats = implode(',',$cat_id);

		// перемещаемые файлы
		$sql = "SELECT i.id, CONCAT(g.parent_url, g.url, '/') as url, i.`name` FROM ".$this->table."_items i INNER JOIN ".$this->table." g ON g.id = i.parent WHERE i.id IN (".$cats.")";
		$files = $this->db->db_dataset_array($sql);

		// путь к папке куда переместить
		$sql = "SELECT CONCAT(parent_url, url, '/') as url FROM ".$this->table." WHERE id = ".$data['gallery_id'];
		$new_folder = $this->db->db_get_value($sql);
		
		// создать папки images  и  thumbs для новой папки
		create_dir($this->path_folder.$new_folder.'images/');
		create_dir($this->path_folder.$new_folder.'thumbs/');
		
		// физическое перемещение файлов
		for ($i=0; $i<count($files); $i++){
			rename($this->path_folder.$files[$i]['url'].'images/'.$files[$i]['name'], $this->path_folder.$new_folder.'images/'.$files[$i]['name']);
			rename($this->path_folder.$files[$i]['url'].'thumbs/'.$files[$i]['name'], $this->path_folder.$new_folder.'thumbs/'.$files[$i]['name']);
		}
		
		// изменение в бд
		$sql = 'UPDATE '.$this->table.'_items SET `parent`='.$data['gallery_id'].' WHERE `id` in ('.$cats.')';
		$this->db->db_query($sql);
	}
	
	
	// --- categories select
	public function getSelectCategories()
	{
		$list = "";
		$this->getCategory(0, 0, $list);
		echo "<SELECT name='gallery_id' onchange=\"moving();\">
		<OPTION value=\"\">-- Select folder --</OPTION>
		<OPTION value=\"0\">".$this->Caption."</OPTION>"
		.$list.
		"\n</SELECT>";
	}
	
	public function getCategory($cId, $level, &$list)
	{
		$sql = "SELECT g.`id`, g.`parent`, g.`node`, gl.`name` FROM ".$this->table." g
		INNER JOIN ".$this->table."_lang gl ON gl.gallery_id = g.id AND gl.lang = ".$_SESSION['lang_id']."
		WHERE g.`parent` = ".$cId." AND g.`disabled`=0";

		$ds = $this->db->db_dataset_array($sql);
	
		for ($i=0; $i<count($ds); $i++)
		{
			if ($ds[$i]['node'] == 1)
			{
				$list .= "\n".'<OPTION value="'.$ds[$i]['id'].'">'.loop($level, "&nbsp;&nbsp;&nbsp;").$ds[$i]['name']."\n</OPTION>";				
				$this->getCategory($ds[$i]['id'], $level + 1, $list);
			}
			else
				$list .= "\n".'<OPTION value="'.$ds[$i]['id'].'">'.loop($level, "&nbsp;&nbsp;&nbsp;").$ds[$i]['name']."\n</OPTION>";
		}
	}	

}
?>
