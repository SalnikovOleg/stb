<?php
class Video extends Module{
	
	public $imgFolder = 'icon_video/';

	function __construct(&$db, &$params)
	{
		parent::__construct($db, $params);
		
		if ($this->realModuleUrl == '') $this->realModuleUrl = 'video';
		
		$this->table = "e_video";
		$this->cache_file = "video.html";
		
		$this->value = $this->query[$this->last];
	}
	
	// загрузка выделенных изображений
	public function LoadBoxContent()
	{
		$sql = "SELECT v.id, v.href, vl.`name`, CASE WHEN v.image='' THEN 'default.jpg' ELSE v.image END AS image, vl.alt FROM ".$this->table." v 
		INNER JOIN ".$this->table."_lang vl ON vl.video_id = v.id AND vl.lang = ".$_SESSION['lang_id']."
		WHERE  v.disabled = 0 AND v.selected = 1";

		$list = $this->db->db_dataset_array($sql);
		
		$this->tpl->assign('icon_folder', $this->path_folder.$this->imgFolder);
		$this->tpl->assign('language', $_SESSION['lang_folder']);
		$this->tpl->assign('see_more_video', langUrl($_SESSION['lang_url']).$this->realModuleUrl.'/');
		$this->tpl->assign('defaultVideoId', $list[0]['href']);
		$this->tpl->assign_by_ref('list', $list);
		$this->tpl->assign('cols', 8);
	
		return $this;
	}
	
	// кталог галерей или галерея
	public function LoadContent()
	{
		$gallery = $this->db->db_get_array("SELECT v.id, vl.`name` FROM ".$this->table." v 
		INNER JOIN ".$this->table."_lang  vl ON vl.video_id = v.id AND vl.lang = ".$_SESSION['lang_id']."
		WHERE v.url = '".$this->value."'");
		
		if ($gallery == null ) {
			$gallery['id'] = 0;
			$preUrl = "";
		}
		else 
			$preUrl = $this->value.'/';
		
		$this->CreateContentTree($gallery['id'], $preUrl);

		$sql = "SELECT href FROM ".$this->table." WHERE href <> '' limit 0,1";
               // $this->tpl->assign('defaultVideoId', $this->db->db_get_value($sql));
		$this->tpl->assign_by_ref('folders', $this->tree);
		$this->tpl->assign('title', $this->Caption.'. '.$gallery['name']);
		$this->tpl->assign('language', $_SESSION['lang_folder']);
		
		$this->GetNavigator();
	
		$this->Content = $this->fetchTemplate(CURRENT_TEMPLATE."modules/".$this->Params['template'], $this->tpl);		
	}
	
	// создание дерева всех галерей для основной страницы галерей
	public function CreateContentTree($id, $url)
	{
		create_dir('./cache/videohref/');
		$file = './cache/videohref/'.$id.'_'.$_SESSION['lang_url'].'_'.$this->cache_file;
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
		$sql = "SELECT n.`id`, n.`parent`, n.`node`, n.`url`, n.image, nl.`alt`, nl.`name`, n.href FROM ".$this->table." n
			INNER JOIN ".$this->table."_lang nl ON nl.video_id = n.id AND nl.`lang`=".$_SESSION['lang_id']."
			WHERE n.`parent` = ".$cId." AND n.`disabled`=0 ORDER BY nl.`name`";

		$ds = $this->db->db_dataset_array($sql);
	
		for ($i=0; $i<count($ds); $i++)
		{
			$url = $this->createUrl($ds[$i]['url'], 0, $preUrl);
				
			if ($ds[$i]['node'] == 1)
			{
				$this->tree .= '<li class="node">'.$ds[$i]['name']."\n";				
				//<a class="node" href="'.$url.'" title="'.$ds[$i]['name'].'">'.$ds[$i]['name']."</a>
				$this->tree .="\n<ul>\n";
				$this->GetContentNode($ds[$i]['id'], $level + 1, $preUrl.$ds[$i]['url'].'/');
				$this->tree .="</ul>\n";
			}
			else
				if (trim($ds[$i]['href']) == ''){
					$this->tree .= '<li class="node">'.$ds[$i]['name']."\n";	
					//$this->tree .= '<a href="'.$url.'" title="'.$ds[$i]['name'].'">'.$ds[$i]['name']."</a>';
					$this->tree .= '</li>\n';
				}
				else {
					$this->tree .= '
					<li>
						<a data-toggle="modal" data-target=".bs-example-modal-lg" id="a-'.$ds[$i]['id'].'" href="javascript:void(0);" title="'.$ds[$i]['name'].'" onclick="viewPlayer(this, \''.$ds[$i]['href'].'\');">
							<img height="80" src="//i.ytimg.com/vi/'.$ds[$i]['href'].'/mqdefault.jpg" alt="" aria-hidden="">
						</a>
						<a data-toggle="modal" data-target=".bs-example-modal-lg" id="a-'.$ds[$i]['id'].'" href="javascript:void(0);" title="'.$ds[$i]['name'].'" onclick="viewPlayer(this, \''.$ds[$i]['href'].'\');">'.$ds[$i]['name']."</a>\n
					</li>\n";				
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
				$sql .= "SELECT ".$i." AS id, gl.name, g.url FROM ".$this->table." g INNER JOIN ".$this->table."_lang gl ON gl.video_id = g.id  WHERE g.url = '".$this->query[$i]."' AND gl.lang=".$_SESSION['lang_id']." ORDER BY 1";
			else
				$sql .= "SELECT ".$i." AS id, gl.name, g.url FROM ".$this->table." g INNER JOIN ".$this->table."_lang gl ON gl.video_id = g.id WHERE g.url = '".$this->query[$i]."' AND gl.lang=".$_SESSION['lang_id']." UNION ";

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
		$this->tpl->assign('icon_folder', $this->path_folder.$this->imgFolder);
		$this->tpl->assign('add_url', '&method=NewItem&pId='.$id);
		$this->tpl->assign('ajax_url', HOST."admin/ajax.php?module=".$params['module']);
		$this->tpl->assign('path_folder', $this->path_folder);
		$this->tpl->assign('selected_action', select_list($this->selected_action, 'action', 'onchange="select_action(this.value, \''.HOST.'admin/ajax.php?module='.$params['module'].'&method=getSelectCategories\');"','',0));		
		
		$this->Content = $this->fetchTemplate(CURRENT_TEMPLATE."modules/video_list.html", $this->tpl);
		$this->AdminNavigator($id);
	}

	// получить список подкатегорий для заданного id
	public function GetItems($id)
	{
		$sql = "SELECT g.`id`, g.`parent`, g.`url` as path, gl.`name`, g.`href`, 
			CASE WHEN g.image='' THEN 'default.jpg' ELSE g.image END AS image,
			CASE WHEN href='' THEN 0 ELSE 1 END as `type`, g.`disabled`, g.`selected`, g.insert_date 
			FROM ".$this->table." g 
			INNER JOIN ".$this->table."_lang gl ON gl.video_id = g.id AND gl.`lang`=".$_SESSION['lang_id']."
			WHERE g.`parent` = ".$id."
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
		$item = $this->db->db_get_array("SELECT g.`id`, g.`parent`, gl.`name` FROM ".$this->table." g INNER JOIN ".$this->table."_lang gl ON gl.video_id = g.id AND gl.lang = ".$_SESSION['lang_id']." WHERE g.id = ".$parent);
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
		
		$item = $this->GetItem($id);
			
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
	private function GetItem($id)
	{
		if ($id != 0)
		{
			$sql = "SELECT g.id, g.parent, g.url, g.disabled, g.selected, g.node, g.image, gl.alt, gl.`name`, g.insert_date, g.href	FROM ".$this->table." g
			INNER JOIN ".$this->table."_lang AS gl ON gl.video_id = g.id AND gl.lang = ".$_SESSION['lang_id']."
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
		$this->insert_folder($data);
		folderDelete('../cache/video');
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
	
		//основная запись 
		$sql = "INSERT INTO ".$this->table." (`parent`, `url`, `parent_url`, `insert_date`, `disabled`, `image`, `href`, `selected`) VALUES("
			.$data['parent'].", '".$url."',	'".$parent_url."', '".date("Y-m-d")."', ".bool_to_int($data, 'disabled').", '".$img."', '".$data['href']."', ".bool_to_int($data, 'selected').")";

		$this->db->db_query($sql);
		
		$id = $this->db->get_insert_id();

		// пометить родительнский раздел как имеющего потомков (node = 1)
		$sql = "UPDATE ".$this->table." SET node = 1 WHERE id = ".$data['parent'];
		$this->db->db_query($sql);
		
		// вставка для всех языков
		$list = $this->db->db_get_list("SELECT id, id FROM a_language WHERE deleted = 0");
		
		$sql = "INSERT INTO ".$this->table."_lang (video_id, lang, `name`, `alt`) VALUES ";
		foreach ($list as $lang)
			$sql .= "(".$id.", ".$lang.", '".$data['name']."', '".$data['alt']."'), ";
		$sql = substr($sql, 0, strlen($sql)-2);

		$this->db->db_query($sql);
	}
	
	//+++++++++++++++++++++++++++++++++++++++++++++++++++++++
	// update
	public function update(&$data)
	{
		$this->update_folder($data);
		folderDelete('../cache/video');		
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
			`image` = '".$img."',
			`href` = '".$data['href']."',
			`selected` = ".bool_to_int($data, 'selected')."
			WHERE id = ".$data['id'];

		$this->db->db_query($sql);

		$sql = "UPDATE ".$this->table."_lang SET 
			`name` = '".$data['name']."',
			`alt` = '".$data['alt']."'
		WHERE video_id = ".$data['id']." AND lang = ".$_SESSION['lang_id'];
		$this->db->db_query($sql);
		
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
		// получить список всех id шников вложеных папок
		$id_list = $data['id'];
		$this->getChieldId($data['id'], $id_list);

		//удалить записи из бд
		$sql = "DELETE FROM ".$this->table." WHERE id IN (".$id_list.")";
		$this->db->db_query($sql);
		$sql = "DELETE FROM ".$this->table."_lang WHERE video_id IN (".$id_list.")";
		$this->db->db_query($sql);
		
		folderDelete('../cache/video');
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
				delFolder($data);
			}
			
		folderDelete('../cache/video');	
	}

	
public function delFile(&$data)
{
	$sql = "DELETE FROM ".$this->table." WHERE id = ".$data['id'];

	$this->db->db_query($sql);
		
	$sql = "DELETE FROM ".$this->table."_lang WHERE video_id = ".$data['id'];
	$this->db->db_query($sql);

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
		
		folderDelete('../cache/video');
	}
	
// перемещение выбраных папок или файлов сразу
	public function moving(&$data)
	{
		$this->moveFolder($data);
		
		folderDelete('../cache/video');	
	}

	
	// перемещение папок
	private function moveFolder(&$data)
	{
		$cat_id=get_id_list_from_post($data,'chk_','-');
		
		if ($cat_id == null) return;

		// изменение в БД
		$cats = implode(',',$cat_id);
		$sql = 'UPDATE '.$this->table.' SET `parent`='.$data['video_id'].' WHERE `id` in ('.$cats.')';
		$this->db->db_query($sql);  
			
		// пометить папку как узловую 
		$sql = 'UPDATE '.$this->table.' SET node = 1 WHERE  id = '.$data['video_id'];
		$this->db->db_query($sql);  
			
		// обновить парентты для всех вложеных папок
		$parent_url = $this->realModuleUrl.'/'.$this->getParentUrl($data['video_id']);
		$this->updateChieldsParentUrl($data['video_id'], $parent_url);
	}
	
	// --- categories select
	public function getSelectCategories()
	{
		$list = "";
		$this->getCategory(0, 0, $list);
		echo "<SELECT name='video_id' onchange=\"moving();\">
		<OPTION value=\"\">-- Select folder --</OPTION>
		<OPTION value=\"0\">".$this->Caption."</OPTION>"
		.$list.
		"\n</SELECT>";
	}
	
	public function getCategory($cId, $level, &$list)
	{
		$sql = "SELECT g.`id`, g.`parent`, g.`node`, gl.`name` FROM ".$this->table." g
		INNER JOIN ".$this->table."_lang gl ON gl.video_id = g.id AND gl.lang = ".$_SESSION['lang_id']."
		WHERE g.`parent` = ".$cId." AND g.`disabled`=0 AND href = ''";

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
