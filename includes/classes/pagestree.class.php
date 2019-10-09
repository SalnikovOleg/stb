<?php
class PagesTree extends Module
{
		protected $tree = "";
		private $sitemap = "";
		protected $folder = "";
		protected $cache_file = "";
		public $current_id = 0;
		public $main_selected_articles_file = "";
		
		public function __construct(&$db, &$params) 
		{
			parent::__construct($db, $params);
			
			$this->cache_file = $this->table."_".$_SESSION['lang_url'].".html";
			$this->sitemap_file = "map_".$this->table."_".$_SESSION['lang_url'].".html";
		}
		
		// загрузка блока статей текущей категории
		public function LoadBoxContent()
		{
			if ( isset($_SERVER['SEARCHED_URL']) && $this->query[$this->last] != $this->module_url) 
				$this->query[$this->last] = $_SERVER['SEARCHED_URL']; // это если задан пользовательский url в таблице references
				
			$sql = 'SELECT CASE IFNULL(a.page_id, 0) WHEN 0 THEN a.id ELSE a.`parent` END AS id	FROM '.$this->table.' a
				WHERE a.url = "'.$this->query[$this->last].'" AND a.lang = '.$_SESSION['lang_id'];
			$current_category = $this->db->db_get_value($sql);

			if ((int)$current_category ==0) // для корневой папки показываем выбранные selected Не показывать кнопку
			{
				$sql = "SELECT n.`name`,  np.description, n.parent_url, n.url, n.absolute_url, n.image, n.image_alt, n.insert_date 
				FROM ".$this->table." n 
				INNER JOIN ".$this->table."_pages np ON np.id = n.page_id
				WHERE n.page_id IS NOT NULL AND n.selected = 1 AND n.lang = ".$_SESSION['lang_id']." AND n.deleted = 0 
				ORDER BY n.insert_date DESC";	
				
				$this->loadArticles($this->main_selected_articles_file, $sql);		
			}
			else{ // для остальных статьи текущей категории
			   
			  $this->file = './cache/articles/curlist_'.$current_category.'_'.$_SESSION['lang_url'].'_articles.html';
			
			 $sql = "SELECT n.`name`,  np.description, n.parent_url, n.url, n.absolute_url, n.image, n.image_alt, n.insert_date 
				FROM ".$this->table." n 
				INNER JOIN ".$this->table."_pages np ON np.id = n.page_id
				WHERE n.page_id IS NOT NULL AND n.parent = ".$current_category." AND n.lang = ".$_SESSION['lang_id']." AND n.deleted = 0
				ORDER BY n.insert_date limit 0, ".ARTICLES_COUNT;
				
				$this->tpl->assign('more_url', $this->mappedModuleUrl); 
				
				$this->loadArticles($this->file, $sql);	
			}
			
			return $this;	
		}
			
			
		// создание списка пунктов меню	
		public function CreateMenu()
		{
			$this->CreateTree(0, '', ' ORDER BY n.name');
			$this->Content = $this->tree;
			return $this->Content;
		}

		public function LoadCategories()
		{
			$this->CreateTree(0, '', ' ORDER BY n.name');
			$this->tpl->assign('list', $this->tree);
			return $this;	
		}
				
		//+++++++++++++    Загрузка страницы по запросу  ++++++++++++++++++++++++++++++++++
		//формирование списка статей или вывод статьи
		public function LoadContent()
		{
			if ( isset($_SERVER['SEARCHED_URL']) && $this->query[$this->last] != $this->module_url) 
				$this->query[$this->last] = $_SERVER['SEARCHED_URL']; // это если задан пользовательский url в таблице references

			if ( $this->query[$this->last] == $this->module_url){
				$this->current_id = 0;
				$this->current_parent = 0;
			    $this->tpl->assign('title', $this->Caption);
				$this->CreateContentTree(0, "");
				$this->tpl->assign('url', HOST.substr($_SERVER['REQUEST_URI'],1));
				$this->tpl->assign('list', $this->tree);				
			}
			else{
				// текуший ID
				$sql = 'SELECT a.id, a.parent, a.disabled FROM '.$this->table.' a	WHERE a.url = "'.$this->query[$this->last].'" AND a.lang = '.$_SESSION['lang_id'].' AND a.deleted = 0 AND a.disabled = 0';
				
				$arr = $this->db->db_get_array($sql);
			/*
			if ($arr['disabled'] == 1)
			{
				//header("HTTP/1.1 301 Moved Permanently");
				
				//header("Location:  ".HOST.$_SESSION['lang_url'].'/'.$this->module_url.(($_SESSION['lang_id'] == 0)?'.html':'/') );
				//exit();
			}
			*/
				$this->current_id = $arr['id'];
				$this->current_parent = $arr['parent'];

				$result = $this->LoadPage($this->query[$this->last]);	
				$this->LoadList();
			}
			
			$this->GetNavigator();	
				
			$this->Content = $this->fetchTemplate(CURRENT_TEMPLATE."modules/".$this->Params['template'], $this->tpl);

		}
			
		// получить текст статьи по url
		protected function LoadPage($url)
		{
			if (isset($Params['text']) && trim($Params['text']) != "")
			{	
				$this->tpl->assign('text', stripslashes($Params['text']));
				$this->tpl->assign('title', $Params['title']);
				return true;
			}	
				
			$page = $this->db->get_page($url, $this->table, $this->table."_pages");

			if ($page !== null)
			{
                $webinarForm = '<div id="reg_form" class="reg_form" style="text-align: center;">
<p style="text-align: center;">Зарегистрироваться сейчас!</p>
<input type="hidden" name="action" value="webinar" /> 
<input type="hidden" name="id_page" value="'.$page['id'].'" /> 
Ф.И.О.<br /> 
<input class="text" type="text" name="call_fio" value="" maxlength="20" /><br /> 
ТЕЛЕФОН<br /> 
<input class="text" type="text" name="call_phone" value="" maxlength="20" /><br /> 
E-MAIL<br /> 
<input class="text" type="text" name="call_email" value="" maxlength="100" /><br />
<div class="order_now">Подать заявку</div></div>';
//                $this->tpl->assign('webinarForm', $webinarForm);
                $text = str_replace('{webinarForm}',$webinarForm, stripslashes($page['text']));

				$this->tpl->assign('title', $page['title']);
				$this->tpl->assign('text', $text);
				if ($page['meta_title'] == "") $page['meta_title'] = $page['title'];
				$this->MetaTitle = $page['meta_title']." - ".META_TITLE;
				$this->MetaDescription = $page['meta_description'];
				$this->MetaKeywords = $page['meta_keywords'];
				return true;
			}
			else
			{
				return false;
				//$Module = new E404("no_page");
				//$Module->LoadContent();
				//$this->tpl->assign('text', $Module->Content);
			}
		}
		
		// получить список подразделов и статей заданного раздела
		protected function LoadList()
		{
				/*
				$sql = 'SELECT a.id, a.`name`, a.`parent`, am.meta_title, am.meta_keywords, am.meta_description 
				FROM '.$this->table.' a
				LEFT JOIN '.$this->table.'_meta am ON am.id = a.id
				WHERE a.id = '.$this->current_id;
				*/
				
				// выбираем мета-теги из таблицы _pages
				$sql = 'SELECT a.id, a.`name`, a.`parent`, am.meta_title, am.meta_keywords, am.meta_description
				FROM '.$this->table.' a
				LEFT JOIN '.$this->table.'_pages am ON am.id = a.page_id
				WHERE a.id = '.$this->current_id;
				
	
				$current = $this->db->db_get_array($sql);
				
				if ($current == null) 
				{
                    $Module = new E404("no_page");
				    $Module->LoadContent();
                    $this->tree = $Module->Content;
                }
                else
                {
					$this->tpl->assign('title', $current['name']);
					if (trim($current['meta_title']) !='')
						$this->MetaTitle = $current['meta_title'];
					else	
						$this->MetaTitle = $current['name']." - ".META_TITLE;
					
					$this->MetaKeywords = $current['meta_keywords'];
					$this->MetaDescription = $current['meta_description'];

					$this->CreateContentTree($current['id'], $this->getParentUrl($current['id']));		
				}

			$this->tpl->assign('url', HOST.substr($_SERVER['REQUEST_URI'],1));
			$this->tpl->assign('list', $this->tree);
		}



		// сформировать список статей
		protected function CreateTree($id=0, $url='', $condition='')
		{
			if (file_exists('./cache/'.$this->cache_file) != false)
				$this->tree = file_get_contents('./cache/'.$this->cache_file);
			else
			{
				$this->tree .= "<ul class=\"dropdown-menu\">\n";
				$this->GetNode($id, 0, $url, $condition);
				$this->tree .= "</ul>\n";
				write_to_file('./cache/'.$this->cache_file, $this->tree);
			}
		}
		
		// получить ветку дерева
		protected function GetNode($cId, $level, $preUrl, $condition, $page_enable = false)
		{
			$ds = $this->db->get_tree_node_by_id($cId, $this->table, $condition);
	
			for ($i=0; $i<count($ds); $i++)
			{
				if ($i == count($ds)-1) $class='class="last"'; else $class='';
				
				$url = $this->createUrl($ds[$i]['url'], $ds[$i]['absolute_url'], $preUrl);				
				
				if ($ds[$i]['node'] == 1)
				{
					$this->tree .= '<li>
					<div class="cat_img">'.($ds[$i]['image']!='' ? "<img src='".DIR_IMAGES.$this->folder."/".$ds[$i]['image']."'>":"&nbsp;").'</div>
					<a '.$class.' href="'.$url.'" title="'.$ds[$i]['name'].'">'.substr($ds[$i]['name'],0,60)."</a>\n";				
					$this->tree .="\n<ul>\n";
					$this->GetNode($ds[$i]['id'], $level + 1, $preUrl.$ds[$i]['url'].'/', $condition);
					$this->tree .="</ul></li>\n";
				}
				//else  // для страницы
					//$this->tree .= '<li><a '.$class.' href="'.HOST.langUrl($_SESSION['lang_url']).$url.'" title="'.$ds[$i]['name'].'">'.substr($ds[$i]['name'],0,60)."</a></li>\n";				
			}
		}
		
		
		// создание дерева всех статей для основной страницы статей новостей сервиса фак
		public function CreateContentTree($id, $url)
		{
			$file = './cache/articles/'.$id.'_'.$this->cache_file;
			if (file_exists($file) != false)
				$this->tree = file_get_contents($file);
			else
			{
				$this->tree .= "<div class=\"articles row\">\n";
				$this->GetContentNode($id, 0, $url);
				$this->tree .= "</div>\n";
				write_to_file($file, $this->tree);
			}			
		}		
		
		// получить ветку дерева для вывода контента
		protected function GetContentNode($cId, $level, $preUrl)
		{
			$ds = $this->db->get_tree_node_by_id($cId, $this->table, ' ORDER BY n.node DESC, n.insert_date DESC ');
				
			for ($i=0; $i<count($ds); $i++)
			{
				$url = $this->createUrl($ds[$i]['url'], $ds[$i]['absolute_url'], $preUrl);
				
				if ($ds[$i]['node'] == 1)
				{
					$this->tree .= '<div class="article-cat col-xs-6 col-sm-6 col-md-3 col-lg-3">
						<div class="cat_img">'.($ds[$i]['image']!='' ? "<img src='".DIR_IMAGES.$this->folder."/".$ds[$i]['image']."'>":"&nbsp;").'</div>
						<a class="node" href="'.$url.'" title="'.$ds[$i]['name'].'">'.$ds[$i]['name']."</a>\n";				
					if ($this->current_parent > 0) { // подкатегории и статьи выводить для всех кроме главной страницы статей
						$this->tree .="\n<ul>\n";
						$this->GetContentNode($ds[$i]['id'], $level + 1, $preUrl.$ds[$i]['url'].'/');
						$this->tree .="</ul>\n";
					}
					$this->tree .= '</div>';
				}
				else {
					
					$this->tree .= "<div class='article-preview row col-xs-12 col-sm-12 col-md-12 col-lg-12'>
					<div class='img col-lg-4'>".($ds[$i]['image']!='' ? "<img src='".DIR_IMAGES.$this->folder."/".$ds[$i]['image']."' alt='".$ds[$i]['image_alt']."' />":"")."</div>
					<div class='article_item col-lg-8".($ds[$i]['image']==''?"without_img":"")."'>
						<a class='name' href='".$url."' title='".$ds[$i]['name']."'>".$ds[$i]['name']."</a>\n
						<div class='description'>".$ds[$i]['description']."</div>
						<a class='more' href='".$url."' title='".$ds[$i]['name']."'>подробнее</a>
					</div>
					<div class='clear'></div>\n</div>";
				}				
			}
		}		
		
		// навигатор положения на сайте
		public function GetNavigator()
		{
			parent::GetNavigator();
		
			if ($this->current_parent == 0) return;

			$file = './cache/articles/nav_'.$this->current_parent.'_'.$_SESSION['lang_url'].'_articles.html';
			if (!file_exists($file)){
			
				$links = array();
				$this->getParentLink($this->current_parent, $links);
			
				$links = array_reverse($links);
				foreach ($links as $item)
					$this->Navigator .= $item;
					
				write_to_file($file, $this->Navigator);	
			}
			else
				$this->Navigator = file_get_contents($file);
				
		}
	
	// получить ссылки на родителей текущей страницы
		private function getParentLink($id, &$links)
		{
			$sql = "SELECT id, url, parent_url, absolute_url, name FROM ".$this->table." WHERE id = ".$id;

			$parent = $this->db->db_get_array($sql);

			$url = $this->createUrl($parent['url'], $parent['absolute_url'], $parent['parent_url'], false);
		
			$links[] =' :: <a href ="'.$url.'">'.$parent['name'].'</a>';
			
			if ($parent['parent'] != 0)
				$this->getParentLink($parent['parent'], $links);
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
	
	//++++++++++++++++++++++  FULLTEXT SEARCH  ++++++++++++++++++++
	// полнотектсовый поиск по текстам модуля
	public function searchText($searchString)
	{
		// выборка преобразование url в зависимости от типа
		$sql = "SELECT p.title, SUBSTRING(p.`text`, 0, 255) as description, t.url, t.absolute_url, MATCH(p.title, p.text) AGAINST('".$searchString."') as rang
		FROM ".$this->table."_pages p
		INNER JOIN ".$this->table." t ON  t.lang = ".$_SESSION['lang_id']." AND t.page_id = p.id  AND t.deleted = 0 AND t.disabled = 0
		WHERE MATCH(p.title, p.text) AGAINST('".$searchString."')  > 0.95" ;
	
		$list = $this->db->db_dataset_array($sql);

		if (count($list) ==0 ) $list = array();

		for ($i=0; $i<count($list); $i++) {
                         if ( trim($list[$i]['title']) == '' ) $list[$i]['title'] = strip_tags(stripslashes($list[$i]['description']));
			$list[$i]['description'] = strip_tags(stripslashes($list[$i]['description'])).'...';
			$list[$i]['url'] = $this->createUrl($list[$i]['url'], $list[$i]['absolute_url']);
		}
		
		return $list;
	}	
	
//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++		
//---------------------------   admin  ------------------------------------------
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
		$this->tpl->assign('edit_url', '&method=EditItem&pId='.$id);
		$this->tpl->assign('add_url', '&method=NewItem&pId='.$id);
		$this->tpl->assign('ajax_url', HOST."admin/ajax.php?module=".$params['module']);
		$this->tpl->assign('selected_action', select_list($this->selected_action, 'action', 'onchange="select_action(this.value, \''.HOST.'admin/ajax.php?module='.$params['module'].'&method=getSelectCategories\');"','',0));		
		
		if (file_exists("./templates/".CURRENT_TEMPLATE."modules/".$this->Params['template']) == true) 
			$this->Content = $this->tpl->fetch(CURRENT_TEMPLATE."modules/".$this->Params['template']);
		else 	
			$this->Content = "Не найден шаблон ".CURRENT_TEMPLATE."modules/".$this->Params['template'];
		
		$this->AdminNavigator($id);
	}

	// получить список подкатегорий для заданного id
	public function GetItems($id)
	{
		$sql = "SELECT n.`id`, n.`parent`, n.`url`, n.`name`, n.`insert_date`, CASE WHEN IFNULL(n.`page_id`, -1) = -1 THEN 0 ELSE 1 END AS `ispage`, n.`disabled`, n.`selected`, n.page_id 
			FROM ".$this->table." n 
			WHERE n.`parent` = ".$id." AND n.`deleted` = 0 AND n.`lang`=".$_SESSION['lang_id']."
			ORDER BY n.disabled, 6, 5";
	
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
		$item = $this->db->db_get_array("SELECT `id`, `parent`, `name` FROM ".$this->table." n WHERE n.id = ".$parent);
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
		
				/*
		$oFCKeditor = new FCKeditor('text');
		$oFCKeditor->BasePath = '../tools/fckeditor/';
		if (isset($item['text']))
			$oFCKeditor->Value = stripslashes($item['text']);
		$oFCKeditor->Height = 400 ;
		$editor=$oFCKeditor->Create();
		*/
		/*$editor = "<a href='http://studybridge/tools/fckeditor/editor/filemanager/browser/default/browser.html?Type=Image&Connector=connectors/php/connector.php' target='_blank'>FileManager</a><br/>
		<textarea name='text' id='text'>".stripslashes($item['text'])."</textarea><script type='text/javascript'>CKEDITOR.replace('text');</script>";
		*/


		$item['text'] = "<textarea name='text' id='text' rows='10' cols='40'>".stripslashes($item['text'])."</textarea>";
		$item['webinar_info'] = "<textarea name='webinar_info' id='webinar_info' rows='10' cols='40'>".stripslashes($item['webinar_info'])."</textarea>";

		if (!isset($item['parent']) ) $item['parent'] =	$params['pId'];	
		
		$this->tpl->assign('listimage', select_value_list(get_file_list('.'.DIR_IMAGES.$this->folder.'/'), 'listimage', '', $item['image'], 0,'-- выберите из загруженных --'));
		$this->tpl->assign('action_title', $params['action_title']);
		$this->tpl->assign('action', $params['action']);
		$this->tpl->assign('addWebinarForm', 'Для того, чтоб добавить форму подписки на вебинар.
		Просто вставьте этот код в поле ТЕКСТ в том месте, где хотите отображать форму.
		<br/>Код - {webinarForm}');
		$this->tpl->assign_by_ref('item', $item);
		$this->tpl->assign('ispage', bool_to_int($params, 'ispage'));
		$this->tpl->assign('module_url', $this->module_url);

		$this->tpl->assign('method_url', $this->method_url);
		$this->tpl->assign('lang_url', $_SESSION['lang_url'].'/');
		$this->tpl->assign('parent_url', '&pId='.$params['pId']);
		//$this->tpl->assign('getlist_url', HOST."admin/ajax.php?module=".$params['module']."&method=getImagesList&pId=".$params['pId']."&itemId=".$id."&folder=content&target_id=listimage");
		$this->tpl->assign('image_folder', DIR_IMAGES.$this->folder.'/');

		$this->tpl->assign('HOST', HOST);
		$this->tpl->assign('pre_url', $this->realModuleUrl.'/'.$this->getParentUrl($params['pId']));
		
		if (file_exists("./templates/".CURRENT_TEMPLATE."modules/".$this->table."_form.html") == true) 
			$this->Content = $this->tpl->fetch(CURRENT_TEMPLATE."modules/".$this->table."_form.html");
		else 	
			$this->Content = "Не найден шаблон ".CURRENT_TEMPLATE."modules/".$this->table."_form.html";		
			
		$this->AdminNavigator($params['pId']);	
	}
	


	// получить данные текущей позиции
	private function GetItem($id)
	{
		if ($id != 0)
		{
			$sql = "SELECT n.id, n.parent, n.url, n.node, n.`name`, n.page_id, n.insert_date, n.disabled, n.selected, n.lang, IFNULL(n.image,'') AS image, n.image_alt,
				np.title, np.text, np.webinar_info, np.webinar_theme, IFNULL(np.meta_title, nm.meta_title) as meta_title, IFNULL(np.meta_description, nm.meta_description) as meta_description,
				IFNULL(np.meta_keywords, nm.meta_keywords) as meta_keywords, np.description, n.absolute_url FROM ".$this->table." n
			LEFT JOIN ".$this->table."_meta nm ON nm.id = n.id 	
			LEFT JOIN ".$this->table."_pages AS np ON np.id = n.page_id
			WHERE n.id = ".$id;

			$item = $this->db->db_get_array($sql);
			$item['image_alt'] = stripslashes($item['image_alt']);
		}
		else
		{
			$item = array('url'=>'', 'name'=>'', 'insert_date'=>date("Y-m-d"), 'disabled'=>0, 'lang'=>$_SESSION['lang_id'],
				'selected'=>'', 'title'=>'', 'meta_title'=>'', 'node'=>0, 'meta_description' =>'', 'meta_keywords'=>'', 'description'=>'', 'image'=>'', 'image_alt'=>'');
		}
		return $item;
	}
	
//--------------------------- save  data  ---------------------------------
	// add
	public function insert(&$data)
	{
		// загрузка рисунка
		$uploaded = false;
		$img = img_upload($data, '.'.DIR_IMAGES.$this->folder.'/', "", $uploaded);
		
		if (trim($data['url']) == '')
			$url = createUrl(utf2str($data['name'], "w"), 0, '');
		else	
			$url = createUrl($data['url'], 0, '');
		
		$pageId = 'null';
		// вставка контента страницы если это страница а не раздел
		if ($data['ispage'] == 1)
		{
			$sql = "INSERT INTO ".$this->table."_pages (`title`, `text`, `meta_title`, `meta_description`, `meta_keywords`, `description`, `webinar_info`, `webinar_theme`) VALUES (
			'".$data['name']."', '".addslashes($data['text'])."', '".$data['meta_title']."', '".$data['meta_description']."', '".$data['meta_keywords']."', 
			'".addslashes($data['description'])."', 
			'".addslashes($data['webinar_info'])."', 
			'".addslashes($data['webinar_theme'])."')";

			$this->db->db_query($sql);
			$pageId = $this->db->get_insert_id();
	
			// в таблице ссылок обновить item_id 
			$this->db->db_query("UPDATE `references` SET `item_id` = ".$pageId." WHERE `url` = '".$data['url']."'");
		}

		// получить родительский url
		$parent_url = $this->realModuleUrl.'/'.$this->getParentUrl($data['parent']);
		
		// вставка данных раздела
		$sql = "INSERT INTO ".$this->table." (`parent`, `url`, `parent_url`, `name`, `page_id`, `insert_date`, `disabled`, `lang`, `selected`, `image`, `image_alt`, `absolute_url`) VALUES("
		.$data['parent'].", '".$url."',	'".$parent_url."', '".$data['name']."', ".$pageId.", '".str_to_date($data['insert_date'])."', 
		".bool_to_int($data, 'disabled').", ".$data['lang'].", ".bool_to_int($data, 'selected').", '".$img."', '".addslashes($data['image_alt'])."', ".$data['absolute_url'].")";

		$this->db->db_query($sql);

		// пометить родительнский раздел как имеющего потомков (node = 1)
		if ($pageId > 0)	{	
			$sql = "UPDATE ".$this->table." SET node = 1 WHERE id = ".$data['parent'];
			$this->db->db_query($sql);
		}
		else{ //если вставка раздела а не страницы то вставить мета теги раздела
				
			$id = $this->db->get_insert_id();
		
			$sql = "INSERT INTO ".$this->table."_meta (id, meta_title, meta_description, meta_keywords) VALUES (".$id.", '".$data['meta_title']."', '".$data['meta_keywords']."', '".$data['meta_description']."')";
			$this->db->db_query($sql);
		}
		
		//если абсолютный url вставить запись в таблицу references
		if ($data['absolute_url'] == 1)
			$this->insertReference($data['url'], $this->module_id);
			
		$this->flush_cache();
	}
	
	// update
	public function update(&$data)
	{
		$uploaded = false;
		$img = img_upload($data, '.'.DIR_IMAGES.$this->folder.'/', "", $uploaded);

		if (trim($data['url']) == '')
			$url = createUrl(utf2str($data['name'], "w"), 0, '');
		else	
			$url = createUrl($data['url'], 0, '');

		if ($data['ispage'] == 1)
		{
			$sql = "UPDATE ".$this->table."_pages SET
				`title` = '".$data['name']."',
				`text` = '".addslashes($data['text'])."',
				`webinar_info` = '".addslashes($data['webinar_info'])."',
				`webinar_theme` = '".addslashes($data['webinar_theme'])."',
				`meta_title` = '".$data['meta_title']."',
				`meta_description` = '".$data['meta_description']."',
				`meta_keywords` = '".$data['meta_keywords']."',
				`description` = '".addslashes($data['description'])."'
			WHERE id = ".$data['page_id'];

			$this->db->db_query($sql);
		}
		else {
			$sql = "
				INSERT INTO ".$this->table."_meta (id, meta_title, meta_description, meta_keywords) VALUES
				(".$data['id'].", '".$data['meta_title']."', '".$data['meta_keywords']."', '".$data['meta_description']."')
				ON DUPLICATE KEY UPDATE 
				`meta_title` = '".$data['meta_title']."',
				`meta_keywords` = '".$data['meta_keywords']."',
				`meta_description` = '".$data['meta_description']."'";
				
			$this->db->db_query($sql);	
		}

		//определить цепочку родительских url
		$parent_url = $this->realModuleUrl.'/'.$this->getParentUrl($data['parent']);
		
		$sql = "UPDATE ".$this->table." SET
			`url` = '".$url."',
			`parent_url` = '".$parent_url."',
			`name` = '".$data['name']."',
			`insert_date` = '".str_to_date($data['insert_date'])."',
			`disabled` = ".bool_to_int($data, 'disabled').",
			`selected` = ".bool_to_int($data, 'selected').",
			`image` = '".$img."', 
			`image_alt` = '".addslashes($data['image_alt'])."',
			`absolute_url` = ".$data['absolute_url']."
		WHERE id = ".$data['id'];

		$this->db->db_query($sql);
		
		//если абсолютный url вставить запись в таблицу references
		if ($data['absolute_url'] == 1)
			$this->insertReference($data['url'], $this->module_id);
			
		//изменить родительские урл для всех подчиненных разделов и статей
		if ($data['node'] == 1)
			$this->updateChieldsParentUrl($data['id'], $parent_url.$data['url'].'/');
		
		$this->flush_cache();
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
	
//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
//---------------  каталог рисунков -------------------
/*/-++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	public function getImagesList(&$params)
	{
		$tpl = new AdminTemplate;
		
		$list_images = get_file_list('.'.DIR_IMAGES.$params['folder'].'/');
	
		$tpl->assign_by_ref('list', $list_images);
		$tpl->assign('folder', $params['folder']);
		$tpl->assign('target_id', $params['target_id']);
		$tpl->assign('action', 'imagesDelete');
		$tpl->assign('image_path', '.'.DIR_IMAGES.$params['folder'].'/');
		$tpl->assign('delete_action', HOST_ADMIN.'?module='.$params['module'].'&method=EditItem&pId='.$params['pId'].'&itemId='.$params['itemId'].'&ispage=1');
		
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
	

//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
// массовые действия со статьями
//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
*/
// удаление нескольких статей или категорий сразу
	public function deleting(&$data)
	{
		$cat_id=get_id_list_from_post($data,'chk_cat-','-');
		if ($cat_id != null) 
		{
			$cat_id = implode(',',$cat_id);
			$sql = 'UPDATE '.$this->table.' SET deleted = 1  WHERE `id` in ('.$cat_id.')';
			$this->db->db_query($sql);
		}	
		
		$this->flush_cache();
	}

// переключение включения отключения категорий и товаров
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
		
		$this->flush_cache();
	}
	
// перемещение нескольких продуктов сразу
	public function moving(&$data)
	{
		$cat_id=get_id_list_from_post($data,'chk_cat-','-');
	
		if ($cat_id != null)
		{
			$cats = implode(',',$cat_id);
			$sql = 'UPDATE '.$this->table.' SET `parent`='.$data['category_id'].' WHERE `id` in ('.$cats.')';
			$this->db->db_query($sql);  
			$sql = 'UPDATE '.$this->table.' SET node = 1 WHERE  id = '.$data['category_id'];
			$this->db->db_query($sql);  
		}
		
		$parent_url = $this->realModuleUrl.'/'.$this->getParentUrl($data['category_id']);
		$this->updateChieldsParentUrl($data['category_id'], $parent_url);
		
		$this->flush_cache();
	}

	
	// --- categories select
	public function getSelectCategories()
	{
		$list = "";
		$this->getCategory(0, 0, $list);
		echo "<SELECT name='category_id' onchange=\"moving();\">
		<OPTION value=\"\">-- Выберите категорию --</OPTION>
		<OPTION value=\"0\">".$this->Caption."</OPTION>"
		.$list.
		"\n</SELECT>";
	}
	
	public function getCategory($cId, $level, &$list)
	{
		$sql = "SELECT c.`id`, c.`parent`, c.`node`, c.`name` FROM ".$this->table." c
		WHERE c.`parent` = ".$cId." AND c.`disabled`=0 AND c.`deleted` = 0 AND c.`lang`=".$_SESSION['lang_id'].' AND page_id IS NULL';

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
