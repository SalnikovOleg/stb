<?php
class Programm extends Module {
	
	protected $method = 'view'; 
	private $parameters = array();
	
	function __construct(&$db, &$params)
	{

		parent::__construct($db, $params);
		$this->table = "e_category_country";
				
		if ($this->realModuleUrl == "") $this->realModuleUrl = 'programm';
		
		define('DIR_IMG_PROGRMM', '/upload/image/programm/');
	}
	
	//+++++++++++++++++++++++++++++++++++++++++++++
	public function LoadBoxContent(){	}

	
	//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	public function LoadContent()
	{
		if (method_exists($this, $this->method)){
			$method = $this->method;
			$this->$method();
		}
		
	}
	
	
	private function view()
	{
		$url = substr($_SERVER['REQUEST_URI'], strpos($_SERVER['REQUEST_URI'],'programm/')+9);
		$page = $this->getProgramm($url);

		if (!$page){
            $Module = new E404("no_page");
			$Module->LoadContent();
            $page['text'] = $Module->Content;
			$this->tpl->assign_by_ref('country', $page);
            $this->Content = $this->fetchTemplate(CURRENT_TEMPLATE."modules/".$this->Params['template'], $this->tpl);
		}
	
		$this->MetaTitle = $page['meta_title'];
		$this->MetaKeywords = $page['meta_keywords'];
		$this->MetaDescription = $page['meta_description'];
		
		includeModule('School');
	      $p = array('Name'=>'School');
	      $school = new School($this->db, $p);         
		$school_table = $school->getCountrySchoolForProgramm($page['country_id'], $page['category_id']);
        	
		$categorys = $this->getCategoryCountry($page['category_id']);

		$this->tpl->assign_by_ref('country', $page);	
		$this->tpl->assign_by_ref('school', $school_table);
		$this->tpl->assign_by_ref('categorys', $categorys);
		$this->tpl->assign('lang_url', langUrl($_SESSION['lang_url']));
		$this->tpl->assign('language', $_SESSION['lang_folder']);
		$this->tpl->assign('dir_img', DIR_IMG_PROGRMM);		
		
		$country_url = $page['absolute_url'] == 1 ? $page['country_url'] : langUrl($_SESSION['lang_url']). 'country/' . $page['country_url'];
		$no_ext = substr_compare( $categorys[0]['category_url'], '.html', -strlen( '.html' ) );
		$category_url = langUrl($_SESSION['lang_url']).'category/'.$categorys[0]['category_url'] . ($no_ext ? '/' : '');	
		$programm_url = langUrl($_SESSION['lang_url']).'programm/'.$page['url'];
		
		$this->Navigator = '<span class="nav"></span>
			<a href="/">Главная</a> > 
			<a href="/'.$category_url.'">'.$categorys[0]['category_name'].'</a> > 
			<a href="/'.$country_url.'">'.$page['country_name'].'</a> > '.$page['name'];			
		
		$_SESSION['last_programm_breadcrumb'] = '<span class="nav"></span>
			<a href="/">Главная</a> > 
			<a href="/'.$category_url.'">'.$categorys[0]['category_name'].'</a> > 
			<a href="'.$country_url.'">'.$page['country_name'].'</a> > 
			<a href="/'.$programm_url.'">'.$page['name'].'</a>';

		$this->Content = $this->fetchTemplate(CURRENT_TEMPLATE."modules/".$this->Params['template'], $this->tpl);
	}
	
	private function getProgramm($url)
	{
		$sql = "SELECT cc.*, cl.name as country_name, cl.url as country_url, cl.absolute_url  
		FROM e_category_country cc 
		 INNER JOIN e_country c ON c.id = cc.country_id 
		 INNER JOIN e_country_lang cl ON cl.country_id = c.id AND cl.lang = ".$_SESSION['lang_id']."
		WHERE cc.lang = ".$_SESSION['lang_id']." AND cc.url = '".$url."' AND cc.disabled = 0";
		$pg = $this->db->db_get_array($sql);
		if (count($pg) ==0)
			return false;
		$pg['text'] = stripslashes($pg['text']);
		$pg['title'] = $pg['name'];
		return $pg;
	}

	// список категорий заданной страны
	public function getCountryCategory($country_id)
	{
		$sql = "SELECT DISTINCT cc.country_id, cc.`name`, cl.`name` as country_name, cc.url
			, catl.`name` as category_name, cc.image , c.image as country_image
		FROM e_category_country cc
			INNER JOIN e_category_lang catl ON catl.category_id = cc.category_id	AND catl.lang = ".$_SESSION['lang_id']."
			INNER JOIN e_category cat ON cat.id = cc.category_id AND cat.disabled = 0 AND cat.deleted = 0
			INNER JOIN e_country c ON c.id = cc.country_id AND c.disabled = 0 AND c.deleted = 0
			INNER JOIN e_country_lang cl ON cl.country_id = cc.country_id AND cl.lang = ".$_SESSION['lang_id']."
		WHERE cc.country_id = ".$country_id." AND cc.lang = ".$_SESSION['lang_id']."
			AND cc.disabled = 0 ORDER BY cc.ordno";

		$list = $this->db->db_dataset_array($sql);
				
		return $list; 		
	}

		
	// cписок стран для заданой категории
	public function getCategoryCountry($category_id)
	{
		$sql = "SELECT DISTINCT cc.country_id, cc.`name`, cl.`name` as country_name, cc.url, catl.url as category_url
			, catl.`name` as category_name, cc.image, c.image as country_image,	catl.name as category_name
		FROM e_category_country cc
			INNER JOIN e_category_lang catl ON catl.category_id = cc.category_id	AND catl.lang = ".$_SESSION['lang_id']."
			INNER JOIN e_category cat ON cat.id = cc.category_id AND cat.disabled = 0 AND cat.deleted = 0
			LEFT JOIN e_category pcat ON pcat.id = cat.parent
			LEFT JOIN e_category_lang pcatl ON pcatl.category_id = pcat.id AND pcatl.lang = ".$_SESSION['lang_id']."
			INNER JOIN e_country c ON c.id = cc.country_id AND c.disabled = 0 AND c.deleted = 0
			INNER JOIN e_country_lang cl ON cl.country_id = cc.country_id AND cl.lang = ".$_SESSION['lang_id']."
		WHERE cc.category_id = ".$category_id." AND cc.lang = ".$_SESSION['lang_id']."
			AND cc.disabled = 0";

		$list = $this->db->db_dataset_array($sql);
				
		return $list; 		
	}
	
	//++++++++++++++++++++++++++++  ADMIN +++++++++++++++++++++++++++++
	public function ItemsList(&$params)
	{
		$this->check_new_programm();
		
		$this->parameters = $params;
		
		$this->module_url = HOST_ADMIN.'?module='.$params['module'];
	
		$list = $this->getItems();

		$this->tpl->assign_by_ref('list', $list);
		$this->tpl->assign('module_url', $this->module_url);
		$this->tpl->assign('HOST', HOST_ADMIN);
		$this->tpl->assign('img_folder', DIR_IMG_PROGRMM);		
		$this->tpl->assign('language', $_SESSION['lang_folder']);
		
		$this->Content = $this->fetchTemplate(CURRENT_TEMPLATE."modules/programm_list.html", $this->tpl);
		
		$this->AdminNavigator("");
	}

	public function getItems()
	{
		$where_category = '';
		$where_country = '';
		if (isset($this->parameters['category_id']))
			$where_category = ' AND cc.category_id = '.(int)$this->parameters['category_id'];
		if (isset($this->parameters['country_id']))
			$where_country = ' AND cc.county_id = '.(int)$this->parameters['country_id'];
			
		$sql = "SELECT cc.id, cc.disabled, cat.name as category_name, cnt.name as country_name, cc.name 
		FROM ".$this->table." cc
			INNER JOIN e_category_lang cat ON cat.category_id = cc.category_id AND cat.lang = ".$_SESSION['lang_id']."
			INNER JOIN e_country_lang cnt ON cnt.country_id = cc.country_id AND cnt.lang = ".$_SESSION['lang_id']."
		WHERE cc.lang=".$_SESSION['lang_id']." ". $where_category . $where_country." ORDER BY cat.name, cnt.name ";

		return $this->db->db_dataset_array($sql);
	}	
	
	private function check_new_programm()
	{
		for($lang = 0; $lang<2; $lang++){
		$sql = "INSERT INTO e_category_country (country_id, category_id, lang, url) 
		SELECT t1.country_id, t1.category_id, ".$lang." as lang,  CONCAT(t1.category_url, '/', t1.country_url) as url
		FROM
		(SELECT DISTINCT scnt.country_id, scat.category_id , cntl.url as country_url, catl.url as category_url
		FROM `e_school_country` scnt
			INNER JOIN `e_school_category` scat ON scat.school_id = scnt.school_id
			INNER JOIN e_country cnt ON cnt.id = scnt.country_id AND cnt.deleted = 0 AND cnt.disabled = 0
			INNER JOIN e_country_lang cntl ON cntl.country_id = cnt.id AND cntl.lang = ".$lang."
			INNER JOIN e_category cat ON cat.id = scat.category_id AND cat.deleted = 0 AND cat.disabled = 0
			INNER JOIN e_category_lang catl ON catl.category_id = cat.id AND catl.lang = ".$lang."
		) t1
		LEFT JOIN (
			SELECT category_id, country_id FROM e_category_country
			WHERE lang = ".$lang."
		) t2 ON t2.category_id = t1.category_id AND t2.country_id = t1.country_id
		WHERE t2.country_id IS NULL AND t2.category_id IS NULL
			";
//echo $sql."<br>";			
			$this->db->db_query($sql);
		}
	}
	
	public function EditItem(&$params)
	{
	
		$this->parameters = $params;	
		
		if (!isset($params['action'])) 
		{
			$params['action'] = 'update';
			$params['action_title'] = 'Edit ';
		}

		$this->module_url = HOST_ADMIN.'?module='.$params['module'];
		
		$item = $this->GetItem();

		$item['text'] = "<textarea name='text' id='text' rows='10' cols='40'>".stripslashes($item['text'])."</textarea>";
		
		$image_list = select_loaded_files('..'.DIR_IMG_PROGRMM, 'listimage', '', $item['image'], 0, '------');
		
		$this->tpl->assign_by_ref('item', $item);
		
		//if( $_SESSION['lang_id'] !== DEFAULT_LANGUAGE )
		$this->tpl->assign('lang_url', $_SESSION['lang_url'].'/');
			
		$this->tpl->assign('action_title', $params['action_title']);
		$this->tpl->assign('action', $params['action']);
		$this->tpl->assign('HOST', HOST);
		$this->tpl->assign('dir_img', DIR_IMG_PROGRMM);
		$this->tpl->assign('image_list', $image_list);
		$this->tpl->assign('module_url', $this->module_url);
		
		$this->Content = $this->fetchTemplate(CURRENT_TEMPLATE."modules/programm_form.html", $this->tpl);
		
		$this->AdminNavigator("");	

	}
	
	private function GetItem()
	{
		if (!isset($this->parameters['id'])) return array();

		$sql = "SELECT cc.*, cc.disabled, cat.name as category_name, cnt.name as country_name 
		FROM ".$this->table." cc
			INNER JOIN e_category_lang cat ON cat.category_id = cc.category_id AND cat.lang = ".$_SESSION['lang_id']."
			INNER JOIN e_country_lang cnt ON cnt.country_id = cc.country_id AND cnt.lang = ".$_SESSION['lang_id']."
		WHERE cc.id = ".(int)$this->parameters['id'];

		return $this->db->db_get_array($sql);
	}
	
	public function update(&$data)
	{
		$uploaded = false;
		$img = img_upload($data, '../'.DIR_IMG_PROGRMM, "", $uploaded);

		if (isset($data['del_image']) && $data['del_image']!= '' && file_exists('../'.DIR_IMG_PROGRMM . $data['del_image'])){
			if ($data['del_image'] == $img)
				$img = '';
			unlink('../'.DIR_IMG_PROGRMM . $data['del_image']);
		}
		
		$sql = "UPDATE ".$this->table." SET
			name ='".quote_replace($data['name'])."',
			url = '".$data['url']."',
			image = '".$img."',
			text = '".addslashes($data['text'])."',
			meta_title = '".quote_replace($data['meta_title'])."',
			meta_keywords = '".quote_replace($data['meta_keywords'])."',
			meta_description = '".quote_replace($data['meta_description'])."',
			disabled = ".bool_to_int($data, 'disabled')." 
		WHERE id = ".(int)$data['id'];
	
		
		$this->db->db_query($sql);
	}
	
	public function Del(&$data)
	{
		if (isset($data['pId'])){
			$sql = "DELETE FROM ".$this->table." where id = ".(int)$data['pId'];
			$this->db->db_query($sql);
		}
	}
}
