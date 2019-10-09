<?php
class Country extends Module{
	
	private $cachefile = "";
	//private $img_folder = "../upload/image/country/";

	private $method = "view";
	public $value = "";
	private $category_url = "";
	
	function __construct(&$db, &$params)
	{
		parent::__construct($db, $params);
	
		$this->table = "e_country";
		$this->keyId = "country_id";
		
		$this->cachefile = "./cache/".$_SESSION['lang_url'].'_e_country.php';
		
		if ($this->realModuleUrl == '') $this->realModuleUrl = 'country';
		if ($this->mappedModuleUrl == '') $this->mappedModuleUrl = $this->realModuleUrl;
		
		if (isset($_SERVER['SEARCHED_URL']) ){
			$this->value =$_SERVER['SEARCHED_URL'];
		}elseif (isset($this->query[MODULEINDEX+2]) && $this->query[MODULEINDEX+2] !=""){
			$this->value = $this->query[MODULEINDEX+2];
		}
		else if (isset($this->query[MODULEINDEX+1]) && $this->query[MODULEINDEX+1] !="")
			$this->value = $this->query[MODULEINDEX+1];

	}
	
	
	//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	public function LoadBoxContent()
	{
		$this->tpl->assign_by_ref('list', $this->getCountryList());
		if (DEFAULT_DOC == 1) $cols = 2;
		if (DEFAULT_DOC == 3) $cols = 1;
		$this->tpl->assign('cols', $cols);
		$this->tpl->assign('title', $this->Caption);
		$this->tpl->assign('url', langUrl($_SESSION['lang_url']).$this->module_url.'/');
		$this->tpl->assign('img_folder', DIR_IMG_COUNTRY);
		
		return $this;
	}


	public function getCountryList($orderBy = 'ORDER BY cl.`name`', $name = 'name')
	{
		$file ="./cache/".$_SESSION['lang_url'].'_'.$name.'_e_country.php';
		if (!file_exists($file)) {
			$list = $this->GetItems($orderBy);
			$content = "<?php\n";
			foreach($list as $item)
				if($item['disabled'] == 0 ){
					$url = $this->createUrl($item['url'], $item['absolute_url']);
					$content .= '$list[]= array("id"=>'.$item["id"].', "name"=>"'.$item["name"].'", "image"=>"'.$item["image"].'", "url"=>"'.$url.'");'."\n";
				}	
			$content .="?>";
			
			write_to_file($file, $content);
			unset($list);
		}	
		include $file;
		
		return $list;
	}

	

	public function	LoadTopBanner()
	{
		$this->tpl->assign_by_ref('list', $this->getCountryList('ORDER BY c.`ordno`', 'ordno'));
		$this->tpl->assign('img_folder', DIR_IMG_COUNTRY);
		
		return $this;
	}

	
	//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	public function LoadContent()
	{
		if (method_exists($this, $this->method) ){
			$method = $this->method;
			$this->$method();
		}

	}
	
	public function CreateMenu()
	{		
		$list = $this->getCountryList();
		$tpl = new Template;
		$tpl->assign_by_ref('list', $list);
		$tpl->assign('url', langUrl($_SESSION['lang_url']).$this->module_url.'/');
		$tpl->assign('img_folder', DIR_IMG_COUNTRY);
				
		$this->Content = "<ul class=\"country_submenu dropdown-menu\"><li>" . $this->fetchTemplate(CURRENT_TEMPLATE."box/country_menu.html", $tpl) . "<br/></li></ul>";
		return $this->Content;
	}
	
	//---------------------------------------------------------------------

	private function view()
	{
		$country = $this->getCountry();
		
		if (!$country){
            $Module = new E404("no_page");
			$Module->LoadContent();
            $country['text'] = $Module->Content;
			$this->tpl->assign_by_ref('country', $country);
            $this->Content = $this->fetchTemplate(CURRENT_TEMPLATE."modules/".$this->Params['template'], $this->tpl);
		}
		
		$this->tpl->assign_by_ref('country', $country);	
		$this->tpl->assign('lang', langUrl($_SESSION['lang_url']));
		$this->tpl->assign('language', $_SESSION['lang_folder']);
		
		includeModule('Programm');
        $p = array('Name'=>'Programm');
        $programm = new Programm($this->db, $p);
        $categorys =  $programm->getCountryCategory($country['id']);

        $this->tpl->assign_by_ref('categorys', $categorys);
		$this->tpl->assign('lang_url', langUrl($_SESSION['lang_url']));
		$this->tpl->assign('dir_img', DIR_IMG_PROGRMM);				
			
		includeModule('Footer_hrefs');
		$p=array();
		$h = new Footer_hrefs($this->db, $p);
		$this->tpl->assign_by_ref('hrefs', $h->get_list());
		
		$this->Navigator = '<span class="nav"></span><a href="/">Главная</a> > '.$country['name'];
		$this->Content = $this->fetchTemplate(CURRENT_TEMPLATE."modules/".$this->Params['template'], $this->tpl);
	}
	
	
	private function getCountry()
	{
		$this->tpl->config_load($_SESSION['lang_folder']."/captions.cfg", 'country');
		$caption = $this->tpl->get_config_vars();

		$sql = "SELECT cl.`name`, c.id, cl.url, cp.title, cp.meta_title, cp.meta_keywords, cp.meta_description, cp.text
		FROM ".$this->table." c 
		INNER JOIN ".$this->table."_lang cl ON cl.country_id = c.id AND cl.url = '".$this->value."' AND cl.lang = ".$_SESSION['lang_id']." 
		LEFT JOIN ".$this->table."_pages cp ON cp.country_id = c.id AND cp.lang = ".$_SESSION['lang_id']."
		WHERE c.deleted = 0 AND c.disabled = 0";

		$country = $this->db->db_get_array($sql);
		
		if (count($country) ==0)
			return false;
		
		$country['text'] = stripslashes($country['text']);
        $country['short_text'] = substr(strip_tags(stripslashes($country['text'])), 0, 600).' ...';
		
		if (trim($country['title']) == '') $country['title'] = $caption['default_title'].' '.$country['name'];
		
		if ($country['meta_title'] != '') $this->MetaTitle = $country['meta_title'];
		else $this->MetaTitle = $caption['default_title'].' '.$country['name'].' - '.META_TITLE;
		if ($country['meta_keywords'] != '') $this->MetaKeywords = $country['meta_keywords'];
		if ($country['meta_description'] != '') $this->MetaDescription = $country['meta_description'];
		
		return $country;
	}
	
	private function getArticleList($country_id)
	{
		$sql = "SELECT a.parent, a.name, CASE a.absolute_url WHEN 1 THEN a.url ELSE CONCAT('".langURL($_SESSION['lang_url'])."', a.parent_url, a.url) END as url,
		ap.description, a.image, a.image_alt, ap.text
		FROM ".$this->table."_articles cp
		INNER JOIN articles a ON a.page_id = cp.page_id
		INNER JOIN articles_pages ap ON ap.id = a.page_id
		WHERE cp.country_id = ".$country_id." AND cp.lang = ".$_SESSION['lang_id']."
		AND a.deleted = 0 AND a.disabled = 0 ORDER BY a.insert_date DESC
		limit 0, ".ARTICLES_COUNT;

		$article_list = $this->db->db_dataset_array($sql);
                if (count($article_list)>0)
	          foreach ($article_list as &$row)
		    $row['text'] = mb_substr(strip_tags($row['text']),0, 255);


		$sql = "SELECT id, url, parent_url, absolute_url, name FROM articles WHERE id = ".$article_list[0]['parent'];

		$parent = $this->db->db_get_array($sql);
		
		$url = $this->createUrl($parent['url'], $parent['absolute_url'], $parent['parent_url'], false);

		$this->tpl->assign('more_url', $url); 
		$this->tpl->assign_by_ref('list', $article_list);
	}



	//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

	public function LoadArticles()
	{
		
		$this->getArticleList($this->getCurrentId());	
		
		$this->tpl->assign('name', $this->Caption);
		$this->tpl->assign('url', langUrl($_SESSION['lang_url']));
		$this->tpl->assign('language', $_SESSION['lang_folder']);
		$this->tpl->assign('folder_img', DIR_IMAGES.'articles');
		$this->tpl->assign('short', true); 

		return $this;
	}

	
	//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
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
		$this->tpl->assign('img_folder', DIR_IMG_COUNTRY);		
		$this->tpl->assign('disable_url', HOST.'admin/ajax.php?module='.$this->Name.'&method=Switche&id=');
		$this->tpl->assign('delete_url', HOST.'admin/ajax.php?module='.$this->Name.'&method=Del&pId=');
		$this->tpl->assign('language', $_SESSION['lang_folder']);
		$this->Content = $this->fetchTemplate(CURRENT_TEMPLATE."modules/country_list.html", $this->tpl);
		
		$this->AdminNavigator("");
	}

	public function GetItems($orderBy = 'ORDER BY cl.`name`')
	{
		$sql = "SELECT c.id, c.ordno, cl.`name`, c.image, c.disabled, cl.url, cl.absolute_url FROM `".$this->table."` c
		INNER JOIN `".$this->table."_lang` cl ON c.id = cl.country_id AND lang = ".$_SESSION['lang_id'].
		" WHERE c.deleted = 0 ".$orderBy;
	
		return $this->db->db_dataset_array($sql);
	}
	

	public function EditItem(&$params)
	{
		if (!isset($params['itemId'])) $id = 0; 
		else $id = (int)$params['itemId'];
		
		if (!isset($params['action'])) 
		{
			$params['action'] = 'update';
			$params['action_title'] = 'Редактировать';
		}

		$this->module_url = HOST_ADMIN.'?module='.$params['module'];
		$this->method_url = '&method=ItemsList';
		
		$item = $this->GetItem($id);
		$images = get_file_list('./'.DIR_IMG_COUNTRY);
/*
		$oFCKeditor = new FCKeditor('text');
		$oFCKeditor->BasePath = '../tools/fckeditor/';
		if (isset($item['text']))
			$oFCKeditor->Value = stripslashes($item['text']);
		$oFCKeditor->Height = 400 ;
		$editor=$oFCKeditor->Create();
*/		
		$item['text'] = "<textarea name='text' id='text' rows='10' cols='40'>".stripslashes($item['text'])."</textarea>";
		
		$this->tpl->assign_by_ref('item', $item);
		$this->tpl->assign('images', select_value_list($images, 'listimage', '', $item['image'], 0) );
		$this->tpl->assign('action_title', $params['action_title']);
		$this->tpl->assign('action', $params['action']);
		$this->tpl->assign('module_url', $this->module_url);
		$this->tpl->assign('method_url', $this->method_url);
		$this->tpl->assign('img_folder', DIR_IMG_COUNTRY);
		$this->tpl->assign('language', $_SESSION['lang_folder']);
		$this->tpl->assign('HOST', HOST);
		$this->tpl->assign('lang_url', langUrl($_SESSION['lang_url']));
		$this->tpl->assign('realModuleUrl', $this->realModuleUrl);
		
		$data = array('id'=>$id);
		$this->tpl->assign_by_ref('binded_articles', $this->getBindedArticles($data));
		
		$this->tpl->assign('getCategoryListForm_url', 'module=Country&method=getCategorysListForm&field=country_id');
		$this->tpl->assign('ajax_url', HOST.'admin/ajax.php?');
		//$this->tpl->assign('edit_article_url', HOST_ADMIN.'?module=Articles&method=EditItem&ispage=1');
		
		$this->Content = $this->fetchTemplate(CURRENT_TEMPLATE."modules/country_form.html", $this->tpl);
		
		$this->AdminNavigator("");	
	}
	

	private function GetItem($id)
	{
		if ($id != 0)
		{
			$sql="SELECT c.id, cl.`name`, c.image, c.disabled, cl.url, cl.absolute_url, cp.title, cp.meta_title, cp.meta_keywords, cp.meta_description, cp.`text`
			FROM ".$this->table." c
			LEFT JOIN ".$this->table."_lang AS cl ON cl.country_id = c.id AND cl.lang =".$_SESSION['lang_id']."	
			LEFT JOIN ".$this->table."_pages AS cp ON cp.country_id = c.id AND cp.lang = ".$_SESSION['lang_id']."	
			WHERE c.id = ".$id ;

			$item = $this->db->db_get_array($sql);
		}
		else
			$item = array('id'=>0, 'name'=>'', 'image'=>'', 'url'=>'', 'absolute_url'=>0);

		return $item;
	}	
	

	public function insert(&$data)
	{
		$uploaded = false;
		$img = img_upload($data, DIR_IMG_COUNTRY, "", $uploaded);

		$sql = "INSERT INTO ".$this->table." (image, disabled) 
		VALUES ('".$img."', ".bool_to_int($data, 'disabled').")";
		$this->db->db_query($sql);
		
		$id = $this->db->get_insert_id();
		

		$url = $this->correctUrl($data);
		
	
		if ($data['absolute_url'] == 1)
			$this->insertReference($data['url'], $this->module_id);
		
	
		$langs = $this->loadLangList();
		$sql = "INSERT INTO ".$this->table."_lang (country_id, lang, name, url, absolute_url) VALUES ";
		foreach ($langs as $item)
			$sql .= "(".$id.", ".$item['id'].", '".$data['name']."', '".$url."', ".$data['absolute_url']."),";
		$sql = substr($sql, 0, strlen($sql)-1);
		
		$this->db->db_query($sql);

		if (trim($data['text']) != ''){
			$sql = "INSERT INTO ".$this->table."_pages (country_id, lang, title, meta_title, meta_description, meta_keywords, text) 
			VALUES (".$id.", ".$_SESSION['lang_id'].", '".$data['title']."', '".$data['meta_title']."', '".$data['meta_description']."', '".$data['meta_keywords']."', '".addslashes($data['text'])."')";

			$this->db->db_query($sql);
		}
		
		$this->flush_cache();
	}
	

	public function update(&$data)
	{
		if (isset($data['del'])){
			$img = $this->db->db_get_value("SELECT image FROM ".$this->table." WHERE id = ".$data['id']);
			unlink(DIR_IMG_COUNTRY.$img);
		}	
			
		$uploaded = false;
		$img = img_upload($data, DIR_IMG_COUNTRY, "", $uploaded);

		$sql = "UPDATE ".$this->table." SET image = '".$img."', disabled = ".bool_to_int($data, 'disabled')." WHERE id = ".$data['id'];
		$this->db->db_query($sql);


		$url = $this->correctUrl($data);
		
		$sql = "UPDATE ".$this->table."_lang SET `name` = '".$data['name']."', url = '".$url."', absolute_url = ".$data['absolute_url']." WHERE country_id = ".(int)$data['id']." AND lang = ".$_SESSION['lang_id'];
		$this->db->db_query($sql);
		
	
		if ($data['absolute_url'] == 1)
			$this->insertReference($data['url'], $this->module_id);
		
		if (trim($data['text']) != "")
		{
			$sql = "
			INSERT INTO ".$this->table."_pages (country_id, lang, title, meta_title, meta_keywords, meta_description, text) VALUES
			(".$data['id'].", ".$_SESSION['lang_id'].", '".$data['title']."', '".$data['meta_title']."', '".$data['meta_keywords']."', '".$data['meta_description']."', '".addslashes($data['text'])."')
			ON DUPLICATE KEY
			UPDATE  
				`title` = '".$data['title']."',
				`meta_title` = '".$data['meta_title']."',
				`meta_keywords` = '".$data['meta_keywords']."',
				`meta_description` = '".$data['meta_description']."',
				`text` = '".addslashes($data['text'])."'";

			$this->db->db_query($sql);
		}

		$this->flush_cache();
	}
	
	

}
?>
