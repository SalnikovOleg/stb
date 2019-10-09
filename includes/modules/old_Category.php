<?php
class Category extends Module {
	
	protected $method = 'view'; 
	public $value = "";
	
	function __construct(&$db, &$params)
	{
		parent::__construct($db, $params);
		$this->table = "e_category";
		$this->keyId = "category_id";
		
		$this->cacheCatList = './cache/'.$_SESSION['lang_url'].'_list_e_category.php';
		$this->cacheCatData = "./cache/".$_SESSION['lang_url']."_e_category.php";
		
		if ($this->realModuleUrl == "") $this->realModuleUrl = 'category';

		// значение запрошенное (id или url катего
		if (isset($_SERVER['SERCHED_URL']))
			$this->value = $_SERVER['SERCHED_URL'];
		else	
			if (isset($this->query[MODULEINDEX + 2]) && trim($this->query[MODULEINDEX + 2]) != "")
				$this->value = $this->query[MODULEINDEX + 2];
			else
				if (isset($this->query[MODULEINDEX + 1]))
					$this->value = $this->query[MODULEINDEX + 1];
				else	
					$this->value = '';
	}
	
	//+++++++++++++++++++++++++++++++++++++++++++++
	// загрузка блока
	public function LoadBoxContent()
	{
		$this->tpl->assign_by_ref('list', $this->getCategoryList());
		$this->tpl->assign('CURRENT_TEMPLATE',CURRENT_TEMPLATE);
		$this->tpl->assign('img_folder', DIR_IMAGES.'banners/');
		
		if (isset($this->query[MODULEINDEX]) && $this->module_url == $this->query[MODULEINDEX]){
			if (is_numeric($this->value))
				$where = "id = ".$this->value;
			else
				$where = "cl.url = '".$this->value."'";	

			$category = $this->db->db_get_array("SELECT c.id, c.parent FROM ".$this->table." c INNER JOIN ".$this->table."_lang cl ON cl.category_id = c.id  AND cl.lang = ".$_SESSION['lang_id']." WHERE ".$where);
			$this->tpl->assign('current', '<script type="text/javascript">var cat_id='.$category['id'].'; var parent='.$category['parent'].';</script>');
		}
               else {
                  $this->tpl->assign('current', '<script type="text/javascript">var cat_id=0; var parent=0;</script>');
                }
		return $this;
	}

	// достать список из бд или из кеша
	public function getCategoryList()
	{
		if (!file_exists($this->cacheCatData)) {
			$sql = "SELECT c.parent, c.ordno, CASE WHEN cl.url ='' THEN c.id ELSE cl.url END as url , c.id, c.href, cl.`name`, pl.`name` parent_name, pl.url parent_url, cl.absolute_url 
			FROM ".$this->table." c 
			INNER JOIN ".$this->table."_lang cl ON cl.category_id = c.id AND cl.lang = ".$_SESSION['lang_id']."
			LEFT JOIN ".$this->table." p ON p.id = c.parent
			LEFT JOIN ".$this->table."_lang pl ON pl.category_id = p.id AND pl.lang = ".$_SESSION['lang_id']."
			WHERE c.disabled = 0 AND c.parent <> 0 AND c.deleted = 0 AND p.disabled = 0 AND p.deleted = 0
			ORDER BY 1, 2";

			$list = $this->db->db_dataset_array($sql);

			$content = "<?php \n";
			foreach ($list as $item){
				$url = $this->createUrl($item['url'], $item['absolute_url'], $item['parent_url'].'/');
				$content .= '$list[]= array("id"=>'.$item['id'].', "parent"=>'.$item['parent'].', "url"=>"'.$url.'", "href"=>"'.$item['href'].'", "name"=>"'.$item['name'].'", "parent_name"=>"'.$item['parent_name'].'");'."\n";
			}
			$content .= "\n ?>";
			write_to_file($this->cacheCatData, $content);
		}
		else {
			include $this->cacheCatData;
		}
	
		return $list;
	}


	// список дополнительных статей для страны
	private function getArticleList($id)
	{
		$sql = "SELECT a.parent, a.name, CASE a.absolute_url WHEN 1 THEN a.url ELSE CONCAT('".langURL($_SESSION['lang_url'])."', a.parent_url, a.url) END as url,
		ap.description, a.image, a.image_alt
		FROM ".$this->table."_articles cp
		INNER JOIN articles a ON a.page_id = cp.page_id
		INNER JOIN articles_pages ap ON ap.id = a.page_id
		WHERE cp.category_id = ".$id." AND cp.lang = ".$_SESSION['lang_id']."
		AND a.deleted = 0 AND a.disabled = 0 ORDER BY a.insert_date DESC
		limit 0, ".ARTICLES_COUNT;

		$article_list = $this->db->db_dataset_array($sql);

		// выбрать ссылку на категорию статей для этой страны
		$sql = "SELECT id, url, parent_url, absolute_url, name FROM articles WHERE id = ".$article_list[0]['parent'];
		$parent = $this->db->db_get_array($sql);
		$url = $this->createUrl($parent['url'], $parent['absolute_url'], $parent['parent_url']);
		
		$this->tpl->assign('more_url', $url); 
		$this->tpl->assign_by_ref('list', $article_list);
	}
	
	//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	public function LoadContent()
	{
		if (method_exists($this, $this->method)){
			$method = $this->method;
			$this->$method();
		}
		else
			$this->get404();			
	}
	
	// отображение страницы категории
	private function view()
	{
		if (is_numeric($this->value))
			$where = "c.id = ".$this->value;
		else
			$where = "cl.url = '".$this->value."'";	
	
		$sql = "SELECT c.id, cl.`name`, c.only_page,  pcl.`name` as parent_name,
		cp.meta_title, cp.meta_description, cp.meta_keywords, cp.text, cp.title, cp.description, cp.add_content
		FROM ".$this->table." c 
		INNER JOIN ".$this->table."_lang cl ON cl.category_id = c.id AND cl.lang = ".$_SESSION['lang_id']."
		INNER JOIN ".$this->table."_lang pcl ON pcl.category_id = c.parent AND pcl.lang = ".$_SESSION['lang_id']."
		LEFT JOIN ".$this->table."_pages cp ON cp.category_id = c.id AND cp.lang = ".$_SESSION['lang_id']."
		WHERE cl.url = '".$this->value."' AND cl.lang = ".$_SESSION['lang_id'];

		$category = $this->db->db_get_array($sql);
		$category['text'] = stripslashes($category['text']);
		$category['short_text'] = substr(strip_tags(stripslashes($category['text'])), 0, 600);
		$category['add_content'] = stripslashes($category['add_content']);
		
		// мета данные
		if (trim($category['meta_title']) != "") $this->MetaTitle = $category['meta_title'];
		else $this->MetaTitle = $category['name'].' - '.$category['parent_name'].' - '.META_TITLE;
		if ($category['title'] == '')  $category['title'] = $category['parent_name'].'. '.$category['name'];
		if (trim($category['meta_keywords']) != "") $this->MetaKeywords = $category['meta_keywords'];
		if (trim($category['meta_description']) != "") $this->MetaDescription = $category['meta_description'];
		
		if ($category['only_page'] != 1) {
			$countrys = $this->getCategoryCountry($category['id']);
			$this->tpl->assign_by_ref('countrys', $countrys);
			$this->tpl->assign('img_folder', DIR_IMG_COUNTRY);
			$this->tpl->assign('current_url', $_SESSION['REQUEST_URI']);
			
			// таблицы школ для заданной страны
			includeModule('School');
			$p = array('Name'=>'School');
			$school = new School($this->db, $p);			
		
			$this->tpl->assign_by_ref('schools', $school->getCategorySchool($category['id']));
		}

		$this->tpl->assign_by_ref('category', $category);
		$this->tpl->assign('language', $_SESSION['lang_folder']);

		$this->Content = $this->fetchTemplate(CURRENT_TEMPLATE."modules/".$this->Params['template'], $this->tpl);
	}
	
	// получить список стран связаных с категорией через школы
	private function getCategoryCountry($id)
	{
		$sql = "SELECT DISTINCT scnt.country_id, cl.`name`, c.image FROM e_school_category scat
		INNER JOIN e_school_country scnt ON scnt.school_id = scat.school_id
		INNER JOIN e_country c ON c.id = scnt.country_id AND c.disabled = 0 AND c.deleted = 0
		INNER JOIN e_country_lang cl ON cl.country_id = scnt.country_id AND cl.lang = ".$_SESSION['lang_id']."
		INNER JOIN e_school s ON s.id = scat.school_id
		WHERE scat.category_id = ".$id." AND s.disabled = 0 AND c.disabled = 0 AND s.deleted = 0 AND c.deleted = 0";

		return $this->db->db_dataset_array($sql);		
	}
	
	//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	// загрузка блока статей для выбраной страны
	//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	public function LoadArticles()
	{
		
		// список статей для страны
		$this->getArticleList($this->getCurrentId());	
		
		$this->tpl->assign('name', $this->Caption);
		$this->tpl->assign('url', langUrl($_SESSION['lang_url']));
		$this->tpl->assign('language', $_SESSION['lang_folder']);
		$this->tpl->assign('folder_img', DIR_IMAGES.'articles');
		$this->tpl->assign('more_url', false);
		$this->tpl->assign('short', true); 
		
		return $this;
	}
	
//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++	
//                    АДМ�&#65533;НКА
//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	public function ItemsList(&$params)
	{
		$this->module_url = HOST_ADMIN.'?module='.$params['module'];
		$this->method_url = '&method='.$params['method'];
		$list = $this->GetItems();

		$this->tpl->assign_by_ref('list', $list);
		$this->tpl->assign('module_url', $this->module_url);
		$this->tpl->assign('method_url', $this->method_url);
		$this->tpl->assign('edit_url', '&method=EditItem');
		$this->tpl->assign('add_url', '&method=NewItem');
		$this->tpl->assign('school_url', HOST_ADMIN.'?module=CategorySchool&method=EditItem');		
		$this->tpl->assign('disable_url', HOST.'admin/ajax.php?module='.$this->Name.'&method=Switche&id=');
		$this->tpl->assign('delete_url', HOST.'admin/ajax.php?module='.$this->Name.'&method=Del&pId=');
		$this->tpl->assign('language', $_SESSION['lang_folder']);		
		
		$this->Content = $this->fetchTemplate(CURRENT_TEMPLATE."modules/category_list.html", $this->tpl);
		
		$this->AdminNavigator($params);
		
		unset($country);
	}

	private function GetItems()
	{
		$sql = "SELECT CASE c.parent WHEN 0 THEN id ELSE c.parent END AS node, c.id, c.ordno, cl.`name`, c.disabled, c.parent, c.only_page, 
		CASE IFNULL(href, '') WHEN '' THEN 0 ELSE 1 END as href
		FROM `".$this->table."` c
		INNER JOIN `".$this->table."_lang` cl ON c.id = cl.category_id AND cl.lang = ".$_SESSION['lang_id']."
		WHERE c.deleted = 0
		ORDER BY 1, 3, 2";

		return $this->db->db_dataset_array($sql);
	}
	
	protected function AdminNavigator(&$params)
	{
		parent::AdminNavigator();
		if (isset($params['name']))
			$this->Navigator .= " :: ".$params['name'];
	}
	
	// ++++++++++++++++++++++++++++   редактирование 
	public function EditItem(&$params)
	{
		if (!isset($params['itemId'])) $id = 0; 
		else $id = (int)$params['itemId'];
		
		if (!isset($params['action'])) 
		{
			$params['action'] = 'update';
			$params['action_title'] = '� едактировать';
		}

		$item = $this->GetItem($id);
		
		$this->module_url = HOST_ADMIN.'?module='.$params['module'];
		$this->method_url = '&method=ItemsList';
	/*
		$oFCKeditor = new FCKeditor('text');
		$oFCKeditor->BasePath = '../tools/fckeditor/';
		if (isset($item['text']))
			$oFCKeditor->Value = stripslashes($item['text']);
		$oFCKeditor->Height = 400 ;
		$editor=$oFCKeditor->Create();
	*/	
		$item['text'] = "<textarea name='text' id='text' rows='10' cols='40'>".stripslashes($item['text'])."</textarea>";
 		
                $item['add_content'] = htmlspecialchars(stripslashes($item['add_content']));

		$sql = "
		SELECT 0, '--- Корневой раздел ---'
		UNION
		SELECT c.id, cl.`name` FROM ".$this->table." c INNER JOIN e_category_lang cl ON c.id = cl.category_id AND cl.lang= ".$_SESSION['lang_id']." WHERE c.parent = 0";
		$parents = $this->db->db_get_list($sql);
		
		$this->tpl->assign_by_ref('item', $item);		
		$this->tpl->assign('parents', select_list($parents, 'parent', 'onchange="categorySwitchText(this.value);"', $item['parent']));
		$this->tpl->assign('action_title', $params['action_title']);
		$this->tpl->assign('action', $params['action']);
		$this->tpl->assign('module_url', $this->module_url);
		$this->tpl->assign('method_url', $this->method_url);
		$this->tpl->assign('HOST', HOST);
		$this->tpl->assign('lang_url', langUrl($_SESSION['lang_url']));
		$this->tpl->assign('realModuleUrl', $this->realModuleUrl);
		
		if ($id != 0)
			$this->tpl->assign('fields', $this->getTable($id));
	
		$data = array('id'=>$id);
		$this->tpl->assign_by_ref('binded_articles', $this->getBindedArticles($data));
		
		$this->tpl->assign('language', $_SESSION['lang_folder']);
		
		$this->tpl->assign('getCategoryListForm_url', 'module=Category&method=getCategorysListForm&field=category_id');
		$this->tpl->assign('ajax_url', HOST.'admin/ajax.php?');
		
		$this->Content = $this->fetchTemplate(CURRENT_TEMPLATE."modules/category_form.html", $this->tpl);
		
		$params['name'] = $item['parent_name'].$item['name'];
		$this->AdminNavigator($params);	
	}
	
	// получить данные текущей позиции
	private function GetItem($id)
	{
		if ($id != 0)
		{
			$sql="SELECT c.id, c.`parent`, cl.`name`, c.href, cl.url, cl.absolute_url, c.only_page, c.disabled, cp.title, cp.meta_title, 
			cp.meta_keywords, cp.meta_description, cp.`text`, IFNULL(CONCAT(pcl.`name`, ' :: '), '') as parent_name, c.ordno, cp.add_content
			FROM ".$this->table." c
			INNER JOIN ".$this->table."_lang AS cl ON cl.category_id = c.id AND cl.lang = ".$_SESSION['lang_id']."	
			LEFT JOIN ".$this->table."_pages AS cp ON cp.category_id = c.id AND cp.lang = ".$_SESSION['lang_id']."	
			LEFT JOIN ".$this->table."_lang AS pcl ON pcl.category_id = c.parent AND pcl.lang = ".$_SESSION['lang_id']." AND c.parent <> 0
			WHERE c.id = ".$id ;

			$item = $this->db->db_get_array($sql);
		}
		else
			$item = array('id'=>0, 'disabled'=>0, 'parent'=>'', 'ordno'=>0, 'name'=>'', 'meta_title'=>'', 'meta_keywords'=>'', 'meta_description'=>'', 'text'=>'', 'absolute_url'=>0);

		return $item;
	}	
	
	// вставка записи
	public function insert(&$data)
	{
		$sql = "INSERT INTO ".$this->table." (`parent`, only_page, href, ordno) VALUES (".$data['parent'].", ".bool_to_int($data, 'only_page').", '".$data['href']."', ".$data['ordno'].")";

		$this->db->db_query($sql);
		
		$id = $this->db->get_insert_id();

		// корректировка URL
		$url = $this->correctUrl($data);
		
		// вставка записей для всех языков
		$langs = $this->loadLangList();
		$sql = "INSERT INTO ".$this->table."_lang (category_id, lang, `name`, url, absolute_url) VALUES ";
		foreach ($langs as $item)
			$sql .= "(".$id.", ".$item['id'].", '".$data['name']."', '".$url."', ".$data['absolute_url']."),";
		$sql = substr($sql, 0, strlen($sql)-1);
		$this->db->db_query($sql);
		
		if (trim($data['text']) != ''){
			$sql = "INSERT INTO ".$this->table."_pages (category_id, lang, meta_title, meta_description, meta_keywords, text, add_content) 
			VALUES (".$id.", ".$_SESSION['lang_id'].", '".$data['meta_title']."', '".$data['meta_description']."', '".$data['meta_keywords']."', '".addslashes($data['text'])."', '".addslashes($data['add_content'])."')";

			$this->db->db_query($sql);
		}
	
		//если абсолютный url вставить запись в таблицу references
		if ($data['absolute_url'] == 1)
			$this->insertReference($data['url'], $this->module_id);
			
		file_del($this->cacheCatList);
		file_del('./.'.$this->cacheCatList);
		file_del('./.'.$this->cacheCatData);
	}
	
	// обновление записи
	public function update(&$data)
	{
		$sql = "UPDATE ".$this->table." SET parent = ".$data['parent'].", disabled = ".bool_to_int($data, 'disabled').", ordno = ".$data['ordno'].",
		only_page = ".bool_to_int($data, 'only_page').", href = '".$data['href']."' WHERE id = ".$data['id'];

		$this->db->db_query($sql);

		// корректировка URL
		$url = $this->correctUrl($data);
		
		$sql = "UPDATE ".$this->table."_lang SET url = '".$url."', `name` = '".$data['name']."', absolute_url = ".$data['absolute_url']." WHERE category_id = ".$data['id']." AND lang = ".$_SESSION['lang_id'];
		$this->db->db_query($sql);
		
		if (trim($data['text']) != "")
		{
			$sql = "
			INSERT INTO ".$this->table."_pages (category_id, lang, meta_title, meta_keywords, meta_description, text, add_content) VALUES
			(".$data['id'].", ".$_SESSION['lang_id'].", '".$data['meta_title']."', '".$data['meta_keywords']."', '".$data['meta_description']."', '".addslashes($data['text'])."', '".addslashes($data['add_content'])."')
			ON DUPLICATE KEY
			UPDATE  
                                `title` = '".$data['title']."',
				`meta_title` = '".$data['meta_title']."',
				`meta_keywords` = '".$data['meta_keywords']."',
				`meta_description` = '".$data['meta_description']."',
				`text` = '".addslashes($data['text'])."',
				`add_content` = '".addslashes($data['add_content'])."'
				";

			$this->db->db_query($sql);
		}

		//если абсолютный url вставить запись в таблицу references
		if ($data['absolute_url'] == 1)
			$this->insertReference($data['url'], $this->module_id);
			
		file_del($this->cacheCatList);
		file_del('./.'.$this->cacheCatList);
		file_del('./.'.$this->cacheCatData);
	}


//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
//                поля таблиц школ для категории
//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	// получить таблицу со списками полей
	public function getTable($id)
	{
		// список доступных полей
		$sql = "SELECT f.field, f.`name` FROM e_school_fields f
		LEFT JOIN e_category_school_fields cf ON cf.field = f.field AND cf.category_id = ".$id."
		WHERE lang = ".$_SESSION['lang_id']." AND cf.field IS NULL";
		
		$tpl = new AdminTemplate;
		$tpl->assign_by_ref('fields', $this->db->db_dataset_array($sql));
		
		// cписок назначеных полей
		$tpl->assign_by_ref('list', $this->db->db_dataset_array("SELECT c.field, f.`name` FROM e_category_school_fields c INNER JOIN e_school_fields f ON f.field = c.field WHERE c.category_id = ".$id." AND f.lang = ".$_SESSION['lang_id']." ORDER BY c.ordno"));
		
		$tpl->assign('add_url', HOST.'admin/ajax.php?module=Category&method=addField&id='.$id.'&field=');
		$tpl->assign('del_url', HOST.'admin/ajax.php?module=Category&method=delField&id='.$id.'&field=');
		$tpl->assign('language', $_SESSION['lang_folder']);	
		
		return $this->fetchTemplate("modules/cat_config_table.html", $tpl);
	}
	
	// добавление поля
	public function addField(&$data)
	{
		$sql = "INSERT INTO e_category_school_fields SELECT ".$data['id'].", '".$data['field']."', MAX(ordno)+1 FROM e_category_school_fields WHERE category_id = ".$data['id'];
		$this->db->db_query($sql);
		return $this->getTable($data['id']);
	}
	
	// удаление поля
	public function delField(&$data)
	{
		$sql = "DELETE FROM e_category_school_fields WHERE category_id = ".$data['id']." AND field = '".$data['field']."'";
		$this->db->db_query($sql);
		return $this->getTable($data['id']);
	}	
}
?>