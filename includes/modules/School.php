<?php
class School extends Module{

	private $method = "view"; 
	public $value = ""; 
	public $imgFolder = "icon_univer/";
	public $path_folder = '../upload/image/';
	public $actionimgFolder = 'icon_action/';
	
	function __construct(&$db, &$params)
	{
		parent::__construct($db, $params);
		$this->db = $db;
		$this->table = "e_school";
		$this->keyId = "school_id";		
		
		if ($this->realModuleUrl == "") $this->realModuleUrl = 'school';
		if ($this->mappedModuleUrl == '') $this->mappedModuleUrl = $this->realModuleUrl;
		

		if (isset($_SERVER['SEARCHED_URL']))
			$this->value = $_SERVER['SEARCHED_URL'];
		else {	
			if (isset($this->query[MODULEINDEX + 1])) {
				$this->value = $this->query[MODULEINDEX + 1];
				if ( count($this->query) > (MODULEINDEX + 1))
					for ($i = (MODULEINDEX + 2); $i<=count($this->query); $i++)
						if (isset($this->query[$i]) && trim($this->query[$i]) != '')
							$this->value .= '/'.$this->query[$i];
			}
		}				
	}
	
	public function LoadBoxContent(){}
	
	public function LoadContent()
	{
		if (method_exists($this, $this->method)){
			$method = $this->method;
			$this->$method();
		}
	
	}


	public function LoadCountryArticles()
	{
		$id = $this->getCurrentId();
		if ($id == null) $id =0;
		
		create_dir('./cache/articles');
		
		$file = './cache/articles/'.$id.'_'.$_SESSION['lang_url'].'_articles.php';
		
		if (!file_exists($file)) {
			$sql = "SELECT a.`name`, ap.description, a.parent_url, a.url, a.absolute_url, a.image, a.image_alt, a.insert_date, a.parent 
			FROM ".$this->table."_country s
			INNER JOIN e_country_articles ca ON ca.country_id = s.country_id AND ca.lang = ".$_SESSION['lang_id']."
			INNER JOIN articles_pages ap ON ap.id = ca.page_id
			INNER JOIN articles a ON a.page_id = ap.id
			WHERE s.school_id = ".$id." AND a.lang = ".$_SESSION['lang_id']." AND a.deleted = 0 AND disabled = 0
			ORDER BY a.insert_date DESC limit 0, ".ARTICLES_COUNT;

			$list = $this->db->db_dataset_array($sql);
		
			$content = "<?php\n";
			for ($i=0; $i<count($list); $i++){
				$list[$i]['description'] = stripslashes($list[$i]['description']);
				$list[$i]['insert_date'] = YMDToDMY(substr($list[$i]['insert_date'],0,10));
				$list[$i]['url'] = $this->createUrl($list[$i]['url'], $list[$i]['absolute_url'], $list[$i]['parent_url'], false);

				$content .= '$list[]=array("name"=>"'.$list[$i]['name'].'", "description"=>"'.$list[$i]['description'].'", "url"=>"'.$list[$i]['url'].'", "image"=>"'.$list[$i]['image'].'", "image_alt"=>"'.$list[$i]['image_alt'].'", "insert_date"=>"'.$list[$i]['insert_date'].'");'."\n";
			}	
			

			$sql = "SELECT id, url, parent_url, absolute_url, name FROM articles WHERE id = ".$list[0]['parent'];

			$parent = $this->db->db_get_array($sql);
			$articlesCategoryUrl = $this->createUrl($parent['url'], $parent['absolute_url'], $parent['parent_url']);
				
			$content .= '$articlesCategoryUrl = "'.$url.'"; ?>';
			
			write_to_file($file, $content);
		}
		else {
			include $file;
		}

		$this->tpl->assign('name', $this->Caption);
		$this->tpl->assign('language', $_SESSION['lang_folder']);
		$this->tpl->assign('folder_img', DIR_IMAGES."articles");
		$this->tpl->assign_by_ref('list', $list);
		$maxcount = count($list);
		$this->tpl->assign('maxcount', $maxcount); 
		$this->tpl->assign('more_url', $articlesCategoryUrl); 
		
		return $this;	
	}
	
	
	//++++++++++++++++++++++++++++++++++++++++++++++++

	public function getCountrySchoolForProgramm($country_id, $category_id)
	{		
		$sql = "SELECT count(s.id) cnt 
		FROM ".$this->table." s 
			INNER JOIN ".$this->table."_lang sl ON sl.school_id = s.id AND sl.lang = ".$_SESSION['lang_id']."
			INNER JOIN ".$this->table."_country scnt ON scnt.school_id = s.id AND scnt.country_id = ".$country_id."
			INNER JOIN e_country c ON c.id = scnt.country_id AND c.disabled =0 AND c.deleted = 0
			INNER JOIN ".$this->table."_category scat ON scat.school_id = s.id
				AND scat.category_id = ".$category_id."
			INNER JOIN e_category_lang catl ON catl.category_id = scat.category_id
				AND catl.lang = ".$_SESSION['lang_id']."
			WHERE s.deleted = 0 AND s.disabled = 0";
			
		$items_count = $this->db->db_get_value($sql);
	
		$pageno = get_post_session_value('pageno', 0);
		$items_on_page = get_post_session_value('on_page', 6);	
				
		$limit = "";
		$paging = false;
		
		$paging['items_on_page_list'] =	 array(6=>6,9=>9,12=>12,24=>24,600=>'все');
		if ($items_count > $items_on_page) {
			$paging['page_navigator'] = page_navigator($pageno, $items_count, $items_on_page);
			$paging['items_on_page'] = $items_on_page;
			$paging['pageno'] = $pageno;
			$limit = " LIMIT ".($pageno * $items_on_page).", ".$items_on_page; 
		}

		$sql = "SELECT sl.`name`, sl.school_type, sl.sity, pg.programm, sl.special, sl.age, sl.age2, sl.dates, s.image, sl.alt, lg.language,
		CASE WHEN sl.url = '' THEN s.id ELSE sl.url END as url, sl.absolute_url, s.id, catl.`name` as category_name, catl.category_id, s.name as original_name,
		s.is_action, s.action_from, s.action_to, sl.action_descr, sl.action_img 
		FROM ".$this->table." s 
			INNER JOIN ".$this->table."_lang sl ON sl.school_id = s.id AND sl.lang = ".$_SESSION['lang_id']."
			INNER JOIN ".$this->table."_country scnt ON scnt.school_id = s.id AND scnt.country_id = ".$country_id."
			INNER JOIN e_country c ON c.id = scnt.country_id AND c.disabled =0 AND c.deleted = 0
			INNER JOIN ".$this->table."_category scat ON scat.school_id = s.id
				AND scat.category_id = ".$category_id."
			INNER JOIN e_category_lang catl ON catl.category_id = scat.category_id 
				AND catl.lang = ".$_SESSION['lang_id']."
			LEFT JOIN (
				SELECT s.id, GROUP_CONCAT(cl.`name`) as programm FROM ".$this->table." s 
					INNER JOIN ".$this->table."_category cat ON cat.school_id = s.id
					INNER JOIN e_category_lang cl ON cl.category_id = cat.category_id AND cl.lang = ".$_SESSION['lang_id']."
				GROUP BY s.id
			) pg ON pg.id = s.id	
			LEFT JOIN (
				SELECT s.id, GROUP_CONCAT(CONCAT(ll.`name`, '<br>')) as language FROM ".$this->table." s 
					INNER JOIN ".$this->table."_languages lng ON lng.school_id = s.id
					INNER JOIN e_list_languages ll ON ll.id = lng.languages_id AND ll.lang = ".$_SESSION['lang_id']."
				GROUP BY s.id
			) lg ON lg.id = s.id			
		WHERE s.deleted = 0 AND s.disabled = 0 
		ORDER BY s.ordno desc, s.id desc
		".$limit;

		$list = $this->db->db_dataset_array($sql);
		
		if (count($list) == 0)
			return "";
			

		includeModule('Filter');
		$p = array('Name'=>'Filter');
		$filter = new Filter($this->db, $p);
		
		return $filter->getSchoolTables($list, 'category', $paging);
		
	}

        public function getCountrySchool($country_id, $category_url = '')
        {
// AND catl.url = '".$category_url."'
                $sql = "SELECT sl.`name`, sl.school_type, sl.sity, pg.programm, sl.special, sl.age, sl.age2, sl.dates, s.image, sl.alt, lg.language,
                CASE WHEN sl.url = '' THEN s.id ELSE sl.url END as url, sl.absolute_url, s.id, catl.`name` as category_name, catl.category_id
                FROM ".$this->table." s
                        INNER JOIN ".$this->table."_lang sl ON sl.school_id = s.id AND sl.lang = ".$_SESSION['lang_id']."
                        INNER JOIN ".$this->table."_country scnt ON scnt.school_id = s.id AND scnt.country_id = ".$country_id."
                        INNER JOIN e_country c ON c.id = scnt.country_id AND c.disabled =0 AND c.deleted = 0
                        INNER JOIN ".$this->table."_category scat ON scat.school_id = s.id
                        INNER JOIN e_category_lang catl ON catl.category_id = scat.category_id
                                AND catl.lang = ".$_SESSION['lang_id']."
                        LEFT JOIN (
                                SELECT s.id, GROUP_CONCAT(cl.`name`) as programm FROM ".$this->table." s
                                        INNER JOIN ".$this->table."_category cat ON cat.school_id = s.id
                                        INNER JOIN e_category_lang cl ON cl.category_id = cat.category_id AND cl.lang = ".$_SESSION['lang_id']."
                                GROUP BY s.id
                        ) pg ON pg.id = s.id        
                        LEFT JOIN (
                                SELECT s.id, GROUP_CONCAT(CONCAT(ll.`name`, '<br>')) as language FROM ".$this->table." s
                                        INNER JOIN ".$this->table."_languages lng ON lng.school_id = s.id
                                        INNER JOIN e_list_languages ll ON ll.id = lng.languages_id AND ll.lang = ".$_SESSION['lang_id']."
                                GROUP BY s.id
                        ) lg ON lg.id = s.id                        
                WHERE s.deleted = 0 AND s.disabled = 0";

                $list = $this->db->db_dataset_array($sql);
                
                if (count($list) == 0)
                        return "";
                        

                includeModule('Filter');
                $p = array('Name'=>'Filter');
                $filter = new Filter($this->db, $p);
                
                return $filter->getSchoolTables($list, 'category');
                
        }
	
	public function getActionSchool(){
		
		$sql = "SELECT count(s.id) cnt FROM ".$this->table." s WHERE s.is_action = 1 AND s.deleted = 0 AND s.disabled = 0";
			
		$items_count = $this->db->db_get_value($sql);
	
		$pageno = get_post_session_value('pageno', 0);
		$items_on_page = get_post_session_value('on_page', 10);
				
		$limit = "";
		$paging = false;
		
		$paging['items_on_page_list'] =	 array(10=>10,600=>'все');
		if ($items_count > $items_on_page) {
			$paging['page_navigator'] = page_navigator($pageno, $items_count, $items_on_page);
			$paging['items_on_page'] = $items_on_page;
			$paging['pageno'] = $pageno;
			$limit = " LIMIT ".($pageno * $items_on_page).", ".$items_on_page; 
		}

		$sql = "SELECT sl.`name`, sl.school_type, sl.sity, pg.programm, sl.special, sl.age, sl.age2, sl.dates, s.image, sl.alt, lg.language,
		CASE WHEN sl.url = '' THEN s.id ELSE sl.url END as url, sl.absolute_url, s.id, catl.`name` as category_name, catl.category_id, s.name as original_name,
		s.is_action, s.action_from, s.action_to, sl.action_descr, sl.action_img, s.id 
		FROM ".$this->table." s 
			INNER JOIN ".$this->table."_lang sl ON sl.school_id = s.id  AND sl.lang = ".$_SESSION['lang_id']."
			INNER JOIN ".$this->table."_country scnt ON scnt.school_id = s.id
			INNER JOIN e_country c ON c.id = scnt.country_id AND c.disabled =0 AND c.deleted = 0
			INNER JOIN (SELECT min(category_id) category_id, school_id FROM ".$this->table."_category GROUP BY school_id) scat ON scat.school_id = s.id
			INNER JOIN e_category_lang catl ON catl.category_id = scat.category_id 
				AND catl.lang = ".$_SESSION['lang_id']."
			LEFT JOIN (
				SELECT s.id, GROUP_CONCAT(cl.`name`) as programm FROM ".$this->table." s 
					INNER JOIN ".$this->table."_category cat ON cat.school_id = s.id
					INNER JOIN e_category_lang cl ON cl.category_id = cat.category_id AND cl.lang = ".$_SESSION['lang_id']."
				GROUP BY s.id
			) pg ON pg.id = s.id	
			LEFT JOIN (
				SELECT s.id, GROUP_CONCAT(CONCAT(ll.`name`, '<br>')) as language FROM ".$this->table." s 
					INNER JOIN ".$this->table."_languages lng ON lng.school_id = s.id
					INNER JOIN e_list_languages ll ON ll.id = lng.languages_id AND ll.lang = ".$_SESSION['lang_id']."
				GROUP BY s.id
			) lg ON lg.id = s.id			
		WHERE s.is_action = 1 AND s.deleted = 0 AND s.disabled = 0 ";

		$list = $this->db->db_dataset_array($sql);
		
		if (count($list) == 0)
			return "";
			
        includeModule('Filter');
        $p = array('Name'=>'Filter');
        $filter = new Filter($this->db, $p);
                
        return $filter->getActionSchool($list, 'category', false);
	}
	
	//++++++++++++++++++++++++++++++++++++++++++++++++

	public function getCategorySchool($category_id, $lang_url = '')
	{
//				AND cntl.url = '".$lang_url."'		
		$sql = "SELECT sl.`name`, sl.school_type, sl.sity, pg.programm, sl.special, sl.age, sl.age2, sl.dates,  
		CASE WHEN sl.url = '' THEN s.id ELSE sl.url END as url, sl.absolute_url, s.id, scat.category_id,
		cntl.`name` as country_name, cntl.country_id, s.image, sl.alt, lg.language
		FROM ".$this->table." s 
			INNER JOIN ".$this->table."_lang sl ON sl.school_id = s.id AND sl.lang = ".$_SESSION['lang_id']."
			INNER JOIN ".$this->table."_category scat ON scat.school_id = s.id AND scat.category_id = ".$category_id."
			INNER JOIN ".$this->table."_country scnt ON scnt.school_id = s.id
            INNER JOIN e_country c ON c.id = scnt.country_id AND c.disabled =0 AND c.deleted = 0
			INNER JOIN e_country_lang cntl ON cntl.country_id = scnt.country_id AND cntl.lang = ".$_SESSION['lang_id']."
		LEFT JOIN (
			SELECT s.id, GROUP_CONCAT(cl.`name`) as programm FROM ".$this->table." s 
			INNER JOIN ".$this->table."_category sc ON sc.school_id = s.id AND sc.category_id = ".$category_id."
			INNER JOIN ".$this->table."_category cat ON cat.school_id = s.id
			INNER JOIN e_category_lang cl ON cl.category_id = cat.category_id AND cl.lang = ".$_SESSION['lang_id']."
			GROUP BY s.id
		) pg ON pg.id = s.id
		LEFT JOIN (
			SELECT s.id, GROUP_CONCAT(CONCAT(ll.`name`, '<br/>')) as language FROM ".$this->table." s 
			INNER JOIN ".$this->table."_languages lng ON lng.school_id = s.id
			INNER JOIN e_list_languages ll ON ll.id = lng.languages_id AND ll.lang = ".$_SESSION['lang_id']."
			GROUP BY s.id
		) lg ON lg.id = s.id		
		WHERE s.deleted = 0 AND s.disabled = 0 ORDER BY s.id DESC";

		$list = $this->db->db_dataset_array($sql);
		
		if (count($list) == 0)
			return "";


		includeModule('Filter');
		$p = array('Name'=>'Filter');
		$filter = new Filter($this->db, $p);

		return $filter->getSchoolTables($list, 'country');
	}
	
	//++++++++++++++++++++++++++++++++++++++++++++++++

	private function view()
	{
		if (is_numeric($this->value))
			$where = "s.id = ".$this->value;
		else
			$where = "sl.url = '".$this->value."'";
			

		$sql = "SELECT s.id, sl.`name`, s.`name` as original_name, sl.school_type, 
			sl.sity, sl.age, sl.age2, sl.dates, sl.special,	sp.`text`, sp.text2,
			sp.meta_title, sp.meta_description, sp.meta_keywords ,s.image, sl.video, sl.video_code,
			sl.action_img, sl.action_descr, s.is_action, s.action_from, s.action_to
		FROM ".$this->table." s 
		INNER JOIN ".$this->table."_lang sl ON sl.school_id = s.id AND sl.lang = ".$_SESSION['lang_id']."
		LEFT JOIN ".$this->table."_pages sp ON sp.school_id = s.id AND sp.lang = ".$_SESSION['lang_id']."
		WHERE  ".$where;

		$school = $this->db->db_get_array($sql);

		if (count($school)==0){
            $Module = new E404("no_page");
			$Module->LoadContent();
            $school['text'] = $Module->Content;
			$this->tpl->assign_by_ref('school', $school);
            $this->Content = $this->fetchTemplate(CURRENT_TEMPLATE."modules/".$this->Params['template'], $this->tpl);
  		}		
		else {
		    $school['text'] = stripslashes($school['text']);
		    $school['text2'] = stripslashes($school['text2']);
		    if ($school['action_from']) $school['action_from'] = YMDToDMY($school['action_from']);
			if ($school['action_to']) $school['action_to'] = YMDToDMY($school['action_to']);	
		}
		if ($school['video'] != '' ) {
			$y = explode('###', stripslashes($school['video']));
				$this->tpl->assign_by_ref('video', $y);
		}
		
		if ($school['video_code'] != '') {
			$h = explode('###', stripslashes($school['video_code']));
			$this->tpl->assign_by_ref('video_code', $h);	
		}		
		
		if (trim($school['meta_title']) != "") $this->MetaTitle = $school['meta_title'];
		else $this->MetaTitle = $school['name'].' - '.META_TITLE;
		if (trim($school['meta_keywords']) != "") $this->MetaKeywords = $school['meta_keywords'];
		if (trim($school['meta_description']) != "") $this->MetaDescription = $school['meta_description'];
		
		$this->tpl->assign_by_ref('school', $school);
		$this->tpl->assign_by_ref('languages', $this->getLanguages($school['id']));
		$this->tpl->assign_by_ref('programs', $this->getPrograms($school['id']));
		$this->tpl->assign_by_ref('business', $this->getBusiness($school['id']));
		
		$gallery = $this->getGallery($school['id']);
		$this->tpl->assign_by_ref('gallery', $gallery);
		$this->tpl->assign('folder', $this->path_folder);
		$this->tpl->assign('img_folder', $this->path_folder . $this->imgFolder);
		$this->tpl->assign('actionimgFolder', $this->path_folder . $this->actionimgFolder);
		
		$this->tpl->assign('cols', 4); 
		$this->tpl->assign('total', count($gallery));
		
		$this->tpl->assign('lang_url', langUrl($_SESSION['lang_url']));
		$this->tpl->assign('language', $_SESSION['lang_folder']);

		if (!isset($_SESSION['last_programm_breadcrumb'])) {
		
		// определение положения школы (если школа относится к нескольким категориям и странам выбирается min значение этих параметров)
		$sql = "SELECT cl.name country_name, cl.url country_url, cl.lang_absolute_url,
					catl.name as category_name, CONCAT(pcatl.url, '/', catl.url) as category_url,
					cc.name as program_name , CONCAT('programm/', cc.url) as program_url 
			FROM e_school s
			INNER JOIN (SELECT MIN(s_c.country_id) country_id, s_c.school_id 
					FROM e_school_country s_c 
					WHERE s_c.school_id = ".$school['id']."
					GROUP BY s_c.school_id
			) ctry ON ctry.school_id = s.id
			INNER JOIN e_country_lang cl ON cl.country_id = ctry.country_id AND cl.lang =".$_SESSION['lang_id']."  
			INNER JOIN (SELECT MIN(scat.category_id) category_id, scat.school_id 
					FROM e_school_category scat 
					WHERE scat.school_id = ".$school['id']."
					GROUP BY scat.school_id
			) ctgr ON ctgr.school_id = s.id
			INNER JOIN e_category_lang catl ON catl.category_id = ctgr.category_id AND catl.lang =".$_SESSION['lang_id']."
			INNER JOIN e_category cat ON cat.id = ctgr.category_id
			INNER JOIN e_category_lang pcatl ON pcatl.category_id = cat.parent	AND pcatl.lang = ".$_SESSION['lang_id']."
			INNER JOIN e_category_country cc ON cc.category_id = ctgr.category_id AND cc.country_id = ctry.country_id AND cc.lang = ".$_SESSION['lang_id']." AND  cc.disabled = 0
		WHERE s.id = ".$school['id'];	
		
			$nav = $this->db->db_get_array($sql);

			$category_url = langUrl($_SESSION['lang_url']).'category/'.$nav['category_url'];
			$country_url = $nav['lang_absolute_url'] == 1 ? $nav['country_url'] : langUrl($_SESSION['lang_url']). 'country/' . $nav['country_url'];
			$programm_url = langUrl($_SESSION['lang_url']).$nav['program_url'];
			
			$this->Navigator = '<span class="nav"></span>
				<a href="/">Главная.</a> > 
				<a href="/'.$category_url.'">'.$nav['category_name']. '</a> > 
				<a href="/'.$country_url.'">'.$nav['country_name'].'</a> > 
				<a href="/'.$programm_url.'">'.$nav['program_name'].'</a> >'
				.$school['name'];
		}		
		else {
			$this->Navigator = $_SESSION['last_programm_breadcrumb'] . ' > ' .$school['name'];
		}
		$this->Content = $this->fetchTemplate(CURRENT_TEMPLATE."modules/".$this->Params['template'], $this->tpl);
	}
	

	private function getLanguages($id)
	{
		$sql = "SELECT ll.id, ll.`name` FROM ".$this->table."_languages l INNER JOIN e_list_languages ll ON ll.id = l.languages_id AND ll.lang = ".$_SESSION['lang_id']." WHERE l.school_id = ".$id;
		return $this->db->db_get_list($sql);	
	}
	
 
	private function getPrograms($id)
	{
		$sql = "SELECT sc.category_id as id, cl.`name` FROM ".$this->table."_category  sc
		INNER JOIN e_category_lang cl ON cl.category_id = sc.category_id AND cl.lang = ".$_SESSION['lang_id']."	WHERE sc.school_id = ".$id;
		return $this->db->db_get_list($sql);		
	}
	

	private function getBusiness($id)
	{
		$sql = "SELECT lb.id, lb.`name` FROM ".$this->table."_business b INNER JOIN e_list_business lb ON lb.id = b.business_id AND lb.lang = ".$_SESSION['lang_id']." WHERE b.school_id = ".$id;
		return $this->db->db_get_list($sql);		
	}
	

	private function getGallery($id)
	{
		$sql = "SELECT CONCAT(g.parent_url, g.url, '/') as folder, gi.`name` as image, gil.alt, gil.description 
		FROM ".$this->table."_gallery sg 
		INNER JOIN e_gallery_items gi ON gi.parent = sg.gallery_id AND gi.disabled = 0
		INNER JOIN e_gallery g ON g.id = gi.parent AND g.disabled = 0
		LEFT JOIN e_gallery_items_lang gil ON gil.item_id = gi.id AND gil.lang = ".$_SESSION['lang_id']."
		WHERE sg.school_id = ".$id;

		$list = $this->db->db_dataset_array($sql);
		
		return $list;
	}
	


	//++++++++++++++++++++++  FULLTEXT SEARCH  ++++++++++++++++++++

	public function searchText($searchString)
	{
		$sql = "SELECT  t.name as title, SUBSTRING(p.`text`, 0, 255) as description, t.url, t.absolute_url, MATCH(p.title, p.text) AGAINST('".$searchString."') as rang
		FROM ".$this->table."_pages p
		INNER JOIN ".$this->table."_lang t ON  t.lang = ".$_SESSION['lang_id']." AND t.school_id = p.school_id
		INNER JOIN ".$this->table." s ON s.id = t.school_id AND s.deleted = 0 AND s.disabled = 0
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

//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++	

	public function ItemsList(&$params)
	{
		$this->module_url = HOST_ADMIN.'?module='.$params['module'];
		$this->method_url = '&method=selectSchool';
		
		if (isset($params['country_id']))
			$country_id = (int)$params['country_id'];
		else{
			$params['country_id'] = 1;
			$country_id = 1;
		}
	
		$list = $this->GetItems($country_id);

		includeModule('Country');
		$p = null;
		$country = new Country($this->db, $p);
		$countrys = $country->GetItems();
		
		$this->tpl->assign_by_ref('countrys', $countrys);
		$data = array('country_id'=>$country_id);
		$this->tpl->assign('table', $this->selectSchool($data));
		$this->tpl->assign('module_url', $this->module_url);
		$this->tpl->assign('country_id', $country_id);
		$this->tpl->assign('method_url', $this->method_url);
		$this->tpl->assign('add_url', '&method=NewItem');
		$this->tpl->assign('select_school_url', $this->module_url.'&method=ItemsList&country_id=');
		$this->tpl->assign('language', $_SESSION['lang_folder']);
		
		$this->Content = $this->fetchTemplate(CURRENT_TEMPLATE."modules/school_list.html", $this->tpl);
		
		$this->AdminNavigator($params);
		
		unset($country);
	}

	private function GetItems($country_id)
	{
		$sql = "SELECT s.id, IFNULL(sl.`name`, s.`name`) AS `name`, s.disabled, sl.school_type
		FROM `".$this->table."` s
		INNER JOIN `".$this->table."_lang` sl ON s.id = sl.school_id AND sl.lang = ".$_SESSION['lang_id']."
		INNER JOIN ".$this->table."_country sc ON sc.school_id = s.id AND sc.country_id = ".$country_id."
		WHERE s.deleted = 0
		ORDER BY sl.`name`";

		return $this->db->db_dataset_array($sql);
	}
	

	public function selectSchool(&$data)
	{
		$tpl = new AdminTemplate;
		
		$list = $this->GetItems((int)$data['country_id']);
		
		$tpl->assign('module_url', HOST_ADMIN.'?module='.$this->Name);
		$tpl->assign('method_url', 'method=selectSchool');
		$tpl->assign('edit_url', '&country_id='.$data['country_id'].'&method=EditItem');
		$tpl->assign('disable_url', HOST.'admin/ajax.php?module='.$this->Name.'&method=Switche&id=');
		$tpl->assign('delete_url', HOST.'admin/ajax.php?module='.$this->Name.'&method=Del&pId=');				
		$tpl->assign('language', $_SESSION['lang_folder']);
		
		$tpl->assign_by_ref('list', $list);
		
		$content = $this->fetchTemplate("modules/school_table.html", $tpl);
		return $content;
	}
	
	protected function AdminNavigator(&$params)
	{
		parent::AdminNavigator();
	
		$item = "";
		$country="";
		
		$country = " ::  <a href='".$this->module_url.$this->method_url."'>".$this->db->db_get_value("SELECT `name` FROM e_country_lang WHERE country_id = ".(int)$params['country_id']." AND lang = ".$_SESSION['lang_id'])."</a>";
		if (isset($params['name']))
			$item = " :: ".$params['name'];
			
		$this->Navigator .= $country.$item;
	}
	
	// ++++++++++++++++++++++++++++  
	public function EditItem(&$params)
	{
		if (!isset($params['itemId'])) $id = 0; 
		else $id = (int)$params['itemId'];

		if (!isset($params['country_id']))
			$params['country_id'] = 1;		
			
		if (!isset($params['action'])) {
			$params['action'] = 'update';
			$params['action_title'] = 'edit';
		}


		$item = $this->GetItem($id);
		//if ($item['country_id'] == -1) 
		$item['country_id'] = $params['country_id'];
			
		$this->module_url = HOST_ADMIN.'?module='.$params['module'];
		$this->method_url = '&method=ItemsList';
		
		$this->tpl->assign_by_ref('item', $item);


		includeModule('Dictionary');
		$p = array('Name'=>'Dictionary');
		$d = new Dictionary($this->db, $p);
		

		$this->tpl->assign('business_type', select_list($d->loadList('e_list_business'), 'business_id', 'style="width:200px"', '', -1));
		$this->tpl->assign('business_list', $this->loadList('business', $id));


		$this->tpl->assign('languages_type', select_list($d->loadList('e_list_languages'), 'languages_id', 'style="width:200px"', '', -1));
		$this->tpl->assign('languages_list', $this->loadList('languages', $id));

	
		$this->tpl->assign('school_type', select_list($d->loadList('e_list_schooltype'), 'school_type_id', '', $item['school_type_id'], 1));

		
		includeModule('Category');
		$p = array('Name'=>'Category');
		$c = new Category($this->db, $p);
		
		$this->tpl->assign('category_type', select_list(get_list($c->getCategoryList(), 'id', 'name'), 'category_id', 'style="width:200px"', '', -1));
		$this->tpl->assign('category_list', $this->getHtmlSchoolToSomething('category', $id));
		unset($c);
		

		includeModule('Country');
		$p = array('Name'=>'Country');
		$c = new Country($this->db, $p);
		$countrys = $c->getCountryList();
		$countrys = get_list($countrys, 'id', 'name');
		
	
		$this->tpl->assign('listimage', select_value_list(get_file_list($this->path_folder.$this->imgFolder), 'listimage', '', $item['image'], 0, '-- выберите из загруженных --'));
		$this->tpl->assign('actionlistimage', select_value_list(get_file_list($this->path_folder.$this->actionimgFolder), 'actionlistimage', '', $item['action_img'], 0, '-- выберите из загруженных --'));

		if ($item['video'] != '' ) {
			$y = explode('###', stripslashes($item['video']));
				$this->tpl->assign('youtube_url', $y);
		}
		if ($item['video_code'] != '') {
			$h = explode('###', htmlspecialchars(stripslashes($item['video_code'])));
			$this->tpl->assign('html_code', $h);	
		}
		 
		includeModule('Gallery');
		$p = array('Name'=>'Gallery');
		$g = new Gallery($this->db, $p);
		
		$gallerys = "";
		$g->getCategory(0, 0, $gallerys);
		$gallery_list ="<SELECT name='gallery_id' id='gallery_id'><option value=\"\"> </option>".$gallerys."</select>";
		
		$this->tpl->assign('gallerys', $gallery_list);
		$this->tpl->assign('gallerys_list', $this->getHtmlSchoolToSomething('gallery', $id));
		unset($g);
		
		$this->tpl->assign('country_type', select_list($countrys, 'country_id', 'style="width:200px"', '', -1));
		$this->tpl->assign('country_list', $this->getHtmlSchoolToSomething('country', $id));
		
		$this->tpl->assign('action_title', $params['action_title']);
		$this->tpl->assign('action', $params['action']);
		$this->tpl->assign('module_url', $this->module_url);
		$this->tpl->assign('method_url', $this->method_url);
		$this->tpl->assign('language', $_SESSION['lang_folder']);
		$this->tpl->assign('ajax_url', HOST.'admin/ajax.php?');
		$this->tpl->assign('HOST', HOST);
		$this->tpl->assign('image_folder', $this->path_folder.$this->imgFolder);
		$this->tpl->assign('actionimgFolder', $this->path_folder.$this->actionimgFolder);
		
		$this->tpl->assign('lang_url', langUrl($_SESSION['lang_url']));
		$this->tpl->assign('realModuleUrl', $this->realModuleUrl);
		
		$this->Content = $this->fetchTemplate(CURRENT_TEMPLATE."modules/school_form.html", $this->tpl);
		
		$params['country_id'] = $item['country_id'];
		$params['name'] = $item['original_name'];

		$this->AdminNavigator($params);	
		
		unset($d);
	}
	

	private function GetItem($id)
	{
		if ($id != 0)
		{
			$sql="SELECT s.id, s.`name` AS original_name, sl.url, sl.absolute_url, sl.alt, s.image,
			sl.`name`, s.disabled, sl.sity, sl.school_type_id, sl.age, sl.age2, sl.dates, sl.special,
			sp.title, sp.meta_title, sp.meta_keywords, sp.meta_description, sp.text, sl.video, sl.video_code, sp.text2,
			sl.action_img, sl.action_descr, s.is_action, s.action_from, s.action_to
			FROM ".$this->table." s
			INNER JOIN ".$this->table."_lang AS sl ON sl.school_id = s.id AND sl.lang = ".$_SESSION['lang_id']."	
			LEFT JOIN ".$this->table."_pages AS sp ON sp.school_id = s.id AND sp.lang = ".$_SESSION['lang_id']."	
			WHERE s.id = ".$id ;

			$item = $this->db->db_get_array($sql);
		}
		else
			$item = array('id'=>0, 'disabled'=>0, 'absolute_url'=>0, 'url'=>'', 'origianl_name'=>'', 'name'=>'', 'sity'=>'', 
				'school_type_id'=>1, 'age'=>'', 'age2'=>'', 'dates'=>'', 'special'=>'', 'title'=>'', 'meta_title'=>'', 'meta_keywords'=>'', 'meta_description'=>'',
				'text'=>'','text2'=>'', 'image'=>'', 'action_img'=>'', 'video'=>'', 'video_code'=>'', 'original_name'=>'');
/*
		$oFCKeditor = new FCKeditor('text');
		$oFCKeditor->BasePath = '../tools/fckeditor/';
		if (isset($item['text']))
			$oFCKeditor->Value = stripslashes($item['text']);
		$oFCKeditor->Height = 500 ;
		$editor=$oFCKeditor->Create();
*/		
		$item['text'] = "<textarea name='text' id='text' rows='10' cols='40'>".stripslashes($item['text'])."</textarea>";
		$item['text2'] = "<textarea name='text2' id='text2' rows='3' cols='40'>".stripslashes($item['text2'])."</textarea>";
		$item['action_descr'] = stripslashes($item['action_descr']);
		
		return $item;
	}	
	

	private function loadList($name, $id)
	{
		$sql = "SELECT b.id, b.`name` FROM e_school_".$name." sb INNER JOIN e_list_".$name." b ON b.id = sb.".$name."_id AND b.lang = ".$_SESSION['lang_id']." WHERE sb.school_id = ".$id;
		$list = $this->db->db_dataset_array($sql);
		
		$result = '';
		for ($i=0; $i<count($list); $i++)	
			$result .= '<li><input type="checkbox"  id="'.$name.'_del-'.$list[$i]['id'].'" name="'.$name.'_del-'.$list[$i]['id'].'"/>&nbsp;&nbsp;&nbsp;'.$list[$i]['name'];
	
		return $result;
	}
	
	 
	private function getHtmlSchoolToSomething($name, $id)
	{
		$sql = "SELECT sc.".$name."_id as id, cl.`name` FROM e_school_".$name." sc
		INNER JOIN e_".$name."_lang cl ON cl.".$name."_id = sc.".$name."_id AND cl.lang = ".$_SESSION['lang_id']."
		WHERE sc.school_id = ".$id;
	
		$list = $this->db->db_dataset_array($sql);
	
		$result = '';
		for ($i=0; $i<count($list); $i++)	
			$result .= '<li><input type="checkbox"  id="'.$name.'_del-'.$list[$i]['id'].'" name="'.$name.'_del-'.$list[$i]['id'].'"/>&nbsp;&nbsp;&nbsp;'.$list[$i]['name'];
		
		return $result;
	}
	

	//*******************************

	public function insert(&$data)
	{
		$img = $this->loadImage($data, 220, 220);
		$actionIcon = $this->loadActionIcon($data);
		
		$url = $this->correctUrl($data);
	
		$video = $this->getVideoUrls($data);
					
		$sql = "INSERT INTO ".$this->table." (`name`, `disabled`, `image`, `is_action`, `action_from`, `action_to`)	
			VALUES ('".$data['original_name']."', ".bool_to_int($data, 'disabled').", '".$img."', ".bool_to_int($data, 'is_action').", '".$data['action_from']."', '".$data['action_to']."')";

		$this->db->db_query($sql);

		$id = $this->db->get_insert_id();
		$data['id'] = $id;

	
		if ($data['absolute_url'] == 1)
			$this->insertReference($data['url'], $this->module_id);
		
		if ($data['school_type_id'] == '') $data['school_type_id'] = 0;
		$school_type = $this->db->db_get_value("SELECT `name` FROM ".T_LIST_SCHOOLTYPE."  WHERE id = ".$data['school_type_id']." AND lang = ".$_SESSION['lang_id']);
		if ($school_type == null ) $school_type = '';
		if (trim($data['name']) == "") $data['name'] = $data['original_name'];
		

		$langs = $this->loadLangList();
		$sql = "INSERT INTO ".$this->table."_lang (school_id, lang, `name`, sity, school_type, school_type_id, age, age2, dates, special, url, `absolute_url`, alt, video, video_code, action_descr, action_img) VALUES ";
		foreach ($langs as $item)
			$sql .= "(".$id.", ".$item['id'].", '".$data['name']."', '".$data['sity']."', '".$school_type."', ".$data['school_type_id'].", ".(int)$data['age'].", ".(int)$data['age2'].", '".$data['dates']."', '".$data['special']."', '".$url."', ".$data['absolute_url'].", '".$data['alt']."', '".addslashes($video['youtube'])."', '".addslashes($video['html_code'])."', '".addslashes($data['action_descr'])."', '".$actionIcon."'),";
		$sql = substr($sql, 0, strlen($sql)-1);

		$this->db->db_query($sql);
		
	
		if (trim($data['text']) != ''){
			$sql = "INSERT INTO ".$this->table."_pages (school_id, lang, title, meta_title, meta_description, meta_keywords, text, text2) 
			VALUES (".$id.", ".$_SESSION['lang_id'].", '".$data['title']."', '".$data['meta_title']."', '".$data['meta_description']."', '".$data['meta_keywords']."', '".addslashes($data['text'])."', '".addslashes($data['text2'])."')";

			$this->db->db_query($sql);
		}
		
		$this->insertBind($data, 'languages');
		$this->insertBind($data, 'business');
		$this->insertBind($data, 'category');
		$this->insertBind($data, 'country');
		$this->insertBind($data, 'gallery');
		
	
		$this->galleryHrefUpdate($data);
		$this->galleryHrefDel($data);
	}
	
	//*******************************

	public function update(&$data)
	{
		$img = $this->loadImage($data, 220, 220);
		$actionIcon = $this->loadActionIcon($data);
		
		$url = $this->correctUrl($data);
	
		$video = $this->getVideoUrls($data);
			
		$sql = "UPDATE ".$this->table." SET 
			name = '".$data['original_name']."', 
			disabled = ".bool_to_int($data, 'disabled').", 
			image = '".$img."',
			is_action = ".bool_to_int($data, 'is_action').",
			action_from = '".$data['action_from']."', 
			action_to = '".$data['action_to']."'
		WHERE id = ".$data['id'];
		$this->db->db_query($sql);
		
		$school_type = $this->db->db_get_value("SELECT `name` FROM ".T_LIST_SCHOOLTYPE." WHERE id = ".$data['school_type_id']." AND lang = ".$_SESSION['lang_id']);
		if ($school_type == null ) $school_type = 0;
		if (trim($data['name']) == "") $data['name'] = $data['original_name'];
	
		$sql = "UPDATE ".$this->table."_lang SET 
			`name` = '".$data['name']."',
			`sity` = '".$data['sity']."',
			`school_type` = '".$school_type."',
			`school_type_id` = '".$data['school_type_id']."',
			`age` = ".(int)$data['age'].",
			`age2` = ".(int)$data['age2'].",
			`dates` = '".$data['dates']."',
			`special` = '".$data['special']."',
			`alt` = '".$data['alt']."',
			`url` = '".$url."',
			absolute_url = ".$data['absolute_url'].",
			video = '".addslashes($video['youtube'])."',
			video_code = '".addslashes($video['html_code'])."',
			action_descr = '".addslashes($data['action_descr'])."',
			action_img = '".$actionIcon."'
		WHERE school_id = ".(int)$data['id']." AND lang = ".$_SESSION['lang_id'];

		$this->db->db_query($sql);
		
		if (trim($data['text']) != ''){
			$sql = "INSERT INTO ".$this->table."_pages (school_id, lang, `title`, meta_title, meta_description, meta_keywords, text) 
				VALUES (".(int)$data['id'].", ".$_SESSION['lang_id'].", '".$data['title']."', '".$data['meta_title']."', '".$data['meta_description']."', '".$data['meta_keywords']."', '".addslashes($data['text'])."')
				ON DUPLICATE KEY
				UPDATE
				`title` = '".$data['title']."',
				`meta_title` = '".$data['meta_title']."',
				`meta_keywords` = '".$data['meta_keywords']."',
				`meta_description` = '".$data['meta_description']."',
				`text` = '".addslashes($data['text'])."',
				`text2` = '".addslashes($data['text2'])."'";

			$this->db->db_query($sql);
		}

	
		if ($data['absolute_url'] == 1)
			$this->insertReference($data['url'], $this->module_id);
		
		$this->insertBind($data, 'languages');
		$this->insertBind($data, 'business');
		$this->insertBind($data, 'category');
		$this->insertBind($data, 'country');		
		$this->insertBind($data, 'gallery');
		
		$this->delBind($data, 'languages');
		$this->delBind($data, 'business');
		$this->delBind($data, 'category');
		$this->delBind($data, 'country');
		$this->delBind($data, 'gallery');
		

		$this->galleryHrefUpdate($data);
		$this->galleryHrefDel($data);
	}

	/* get url video from post data www.youtube.com/watch?v=MdkPUmkScec*/
	private function getVideoUrls($data)
	{
		$result = array('youtube' => '', 'html_code' => '');
		if (isset($data['youtube_url'])) {
			foreach ($data['youtube_url'] as $row)
			  if (trim($row) !== ''){
				$result['youtube'] .= createVideoUrl($row).'###';
			}
			$result['youtube'] = substr($result['youtube'], 0, -3);
		}
		
		if (isset($data['html_code'])) {
			foreach ($data['html_code'] as $row)
			  if (trim($row) !== '')
				$result['html_code'] .= $row.'###';
			$result['html_code'] = substr($result['html_code'],0, -3);
		}
			
		return  $result;
	}	
	
	//*******************************

	private function insertBind(&$data, $name)
	{
		$list = get_id_list_from_post($data, $name.'-', '-');

		if (count($list) == 0) return;
		
		$sql = "INSERT INTO e_school_".$name." (school_id, ".$name."_id) VALUES ";
		foreach ($list as $id)
			if (!isset($data[$name.'_del-'.$id]))
				$sql .="(".(int)$data['id'].", ".$id."), ";
		
		$sql = substr($sql, 0, strlen($sql)-2);

		$this->db->db_query($sql);
	}
	

	private function delBind(&$data, $name)
	{
		$list = get_id_list_from_post($data, $name.'_del-', '-');
		if (count($list) == 0) return;
		
		$id = implode(',', $list);
		$sql = "DELETE FROM e_school_".$name." WHERE school_id = ".(int)$data['id']." AND ".$name."_id IN (".$id.")";
		
		$this->db->db_query($sql);
	}
	

	private function galleryHrefUpdate(&$data)
	{
	
		$list = get_id_list_from_post($data, 'gallery-', '-');
		
		if (count($list) == 0) return;
		
		$list = implode(',', $list);
		if ($data['absolute_url'] == 1) $url = $data['url'];
		else $url = langUrl($_SESSION['lang_url']).$this->realModuleUrl.'/'.$data['url'];
		
		$sql = "UPDATE e_gallery_items_lang SET href = CASE WHEN href != '' THEN href ELSE '".$url."' END 
		WHERE lang = ".$_SESSION['lang_id']." AND item_id IN (SELECT id FROM e_gallery_items WHERE parent IN (".$list."))";

		$this->db->db_query($sql);
		
	}
	
	private function galleryHrefDel(&$data)
	{
	
		$list = get_id_list_from_post($data, 'gallery_del-', '-');
		
		if (count($list) == 0) return;
		$list = implode(',', $list);

		$sql = "UPDATE e_gallery_items_lang SET href = '' WHERE lang = ".$_SESSION['lang_id']." AND item_id IN (SELECT id FROM e_gallery_items WHERE parent IN (".$list."))";

		$this->db->db_query($sql);
	}
	
	
	private function loadActionIcon($data){
		$uploaded = false;
		$img_name = img_upload($data, $this->path_folder . $this->actionimgFolder, $pre="action", $uploaded);
		return $img_name; 
	}
}
?>
