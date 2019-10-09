<?php
class Filter extends Module{
	
	private $data = array();
	public $imgFolder = "icon_univer/";
	public $actionimgFolder = "icon_action/";
	
	function __construct(&$db, &$params)
	{
		parent::__construct($db, $params);
		$this->db = $db;
	}
	
	//++++++++++++++++++++++++++++++++++++++++
	// форма фильтра
	public function LoadBoxContent()
	{
		// ктегория
		includeModule('Category');
		$p = array('Name'=>'Category');
		$category = new Category($this->db, $p);

		$category_id = $category->getCurrentId();
		// фильтр выводится если определен кроме страница school, country
		if (!isset($this->query[MODULEINDEX])) $module_link = '';
		else $module_link = $this->query[MODULEINDEX];		
		
		if ($category_id == null && $module_link != 'school'){

                	if ($module_link == $this->module_url && isset($_SESSION['category_id']))
				$category_id = $_SESSION['category_id'];
			else
				$category_id = 0; // id категрии по которой выбираются настройки фильра по умолчанию
		}
		
		$_SESSION['category_id'] = $category_id;

		$sql = 'SELECT cf.`field`, ff.`name` FROM e_category_filter_fields cf INNER JOIN e_filter_fields ff ON ff.field = cf.field AND ff.lang = '.$_SESSION['lang_id'].' WHERE cf.category_id = '.$category_id;
		$filter = $this->db->db_get_list($sql);
		
		if (count($filter) == 0) {
			$this->tpl->assign('visible', false);
			return $this;
		}
		
		$this->tpl->assign('visible', true);
		$this->tpl->assign_by_ref('filter', $filter);
		
		// список программ
		if (isset($filter['program']))
			$this->tpl->assign('program', select_list(get_list($category->getCategoryList(), 'id', 'name'), 'program_id', 'style="width:200px"', (isset($_SESSION['program_id'])?$_SESSION['program_id']:0), -1, '-- Все --'));	
		else
			// если фильтр на странице программы (категория не по умолчанию) 
			if 	($category_id > 0)	
				$this->tpl->assign('program_hidden', '<input type="hidden" name="program_id" value="'.$category_id.'">');
				
		// справочники
		includeModule('Dictionary');
		$p = array('Name'=>'Dictionary');
		$dict = new Dictionary($this->db, $p);

		// список языков
		if(isset($filter['language']))
			$this->tpl->assign('languages', select_list($dict->loadList('e_list_languages'), 'languages_id', 'style="width:200px"', (isset($_SESSION['languages_id'])?$_SESSION['languages_id']:0), -1, '-- Все --'));
			
		// проф область
		if(isset($filter['business']))
			$this->tpl->assign('business', select_list($dict->loadList('e_list_business'), 'busines_id', 'style="width:200px"', $_SESSION['busines_id'], -1, '-- Все --'));
		
		unset($dict);
		
		// страны
		if (isset($filter['country'])){
			includeModule('Country');
			$p = array('Name'=>'Country');
			$country = new Country($this->db, $p);
			$countrys = get_list($country->getCountryList(), 'id', 'name');
			unset($country);
			$this->tpl->assign('country', select_list($countrys, 'country_id', 'style="width:200px"', (isset($_SESSION['country_id'])?$_SESSION['country_id']:0), -1, '-- Все --'));		
		}
		
		// возраст
		if (isset($filter['age'])){
			$this->tpl->assign('age', true);
			$this->tpl->assign('age', $_SESSION['age']);
			$this->tpl->assign('age2', $_SESSION['age2']);
		}	
		
		$this->tpl->assign('action_url', langUrl($_SESSION['lang_url']).$this->realModuleUrl.'/');
		$this->tpl->assign('title', $this->Caption);
		$this->tpl->assign('language_folder', $_SESSION['lang_folder']);
		
		unset($category);
		
		return $this;
		
	}


	//++++++++++++++++++++++++++++++++++++++++	
	// часть SQL выборки по списку
	private function listWhere($name, $param)
	{
		$result = '';
		if (isset($this->data[$param]) && trim($this->data[$param])!=''){
			$_SESSION[$param] = $this->data[$param];
			$result = ' INNER JOIN e_school_'.$name.' ON e_school_'.$name.'.school_id = e_school.id AND e_school_'.$name.'.'.$name.'_id = '.(int)$this->data[$param];
		}	
		else
			unset($_SESSION[$param]);
			
		return $result;	
	}
	
	// часть sql  выборки по возрасту
	private function ageWhere($param, $param2)
	{
		$result = '';
		if (isset($this->data[$param]) && trim($this->data[$param])!=''){
			if ((int)$this->data[$param2] == 0)
				$result =' AND e_school_lang.age = '.(int)$this->data[$param];
			else
				$result =' AND e_school_lang.age >='.(int)$this->data[$param].' AND e_school_lang.age2<='.(int)$this->data[$param2];
		}
		
		$_SESSION[$param] = $this->data[$param];
		$_SESSION[$param2] = $this->data[$param2];
		return $result;
	}
	
	//++++++++++++++++++++++++++++++++++++++++
	// выполнить подбор школ 
	public function LoadContent()
	{
		// определить переданные данные
		if (isset($_POST['submit'])) $this->data = $_POST;
		else $this->data = $_GET;
		
		// список программ	
		$programJoin = $this->listWhere('category', 'program_id');
		if ($programJoin != '') {
			$caption = $this->db->db_get_value("SELECT `name` FROM e_category_lang WHERE category_id = ".$this->data['program_id']." AND lang=".$_SESSION['lang_id']);
			$selected_params['{#program#}'] = $caption;
		}	
		
		// список языков
		$languageJoin = $this->listWhere('languages', 'languages_id');
		if ($languageJoin != '') {
			$caption = $this->db->db_get_value("SELECT `name` FROM e_list_languages WHERE id = ".$this->data['languages_id']." AND lang=".$_SESSION['lang_id']);
			$selected_params['{#language#}'] = $caption;
		}	

		// проф область
		$businesJoin = $this->listWhere('business', 'busines_id');
		if ($businesJoin != '') {
			$caption = $this->db->db_get_value("SELECT `name` FROM e_list_business WHERE id = ".$this->data['busines_id']." AND lang=".$_SESSION['lang_id']);
			$selected_params['{#business#}'] = $caption;
		}	

		// страны
		$countryJoin = $this->listWhere('country', 'country_id');
		if ($countryJoin != '') {
			$caption = $this->db->db_get_value("SELECT `name` FROM e_country_lang WHERE country_id = ".$this->data['country_id']." AND lang=".$_SESSION['lang_id']);
			$selected_params['{#country#}'] = $caption;
		}	
		
		// возраст
		$ageWhere = $this->ageWhere('age', 'age2');
		if ($ageWhere != '') {
			$selected_params['{#age#}'] = $this->data['age'].' - '.$this->data['age2'];
		}	
		
		
		if ( trim($this->data['program_id']) != '' ){
			$sql = "SELECT e_school_lang.`name`, e_school_lang.school_type, e_school_lang.sity, e_school_lang.special, e_school_lang.age, e_school_lang.age2, e_school_lang.dates,  
			CASE WHEN e_school_lang.url = '' THEN e_school.id ELSE e_school_lang.url END as url, e_school.absolute_url, e_school.id, lg.language,
			e_school_category.category_id, e_country_lang.`name` as country_name, e_country_lang.country_id, e_school.image, e_school_lang.alt
			FROM e_school  
			INNER JOIN e_school_lang ON e_school_lang.school_id = e_school.id AND e_school_lang.lang = ".$_SESSION['lang_id']." ".
			$programJoin.$languageJoin.$businesJoin.$countryJoin;
			
			if ($countryJoin =='') $sql .=' INNER JOIN e_school_country ON e_school_country.school_id = e_school.id';
			
			$sql .=" INNER JOIN e_country_lang ON e_country_lang.country_id = e_school_country.country_id AND e_country_lang.lang = ".$_SESSION['lang_id'].
			" LEFT JOIN (
				SELECT s.id, GROUP_CONCAT(ll.`name`) as language FROM e_school s 
				INNER JOIN e_school_languages lng ON lng.school_id = s.id
				INNER JOIN e_list_languages ll ON ll.id = lng.languages_id AND ll.lang = ".$_SESSION['lang_id']."
				GROUP BY s.id
				) lg ON lg.id = e_school.id
			 WHERE e_school.deleted = 0 AND e_school.disabled=0 ".$ageWhere;

			$group_name = 'country';
		}
		else
		if ( trim($this->data['country_id']) != '' ){
		
			$sql = "SELECT e_school_lang.`name`, e_school_lang.school_type, e_school_lang.sity, e_school_lang.special, e_school_lang.age, e_school_lang.age2, e_school_lang.dates,  
			CASE WHEN e_school_lang.url = '' THEN e_school.id ELSE e_school_lang.url END as url, e_school.absolute_url, e_school.id, lg.language,
			e_school_category.category_id, e_category_lang.`name` as category_name, e_school.image, e_school_lang.alt
			FROM e_school  
			INNER JOIN e_school_lang ON e_school_lang.school_id = e_school.id AND e_school_lang.lang = ".$_SESSION['lang_id']." ".
			$languageJoin.$businesJoin.$countryJoin."
			INNER JOIN e_school_category ON e_school_category.school_id = e_school.id
			INNER JOIN e_category_lang ON e_category_lang.category_id = e_school_category.category_id AND e_category_lang.lang = ".$_SESSION['lang_id'].
			" LEFT JOIN (
				SELECT s.id, GROUP_CONCAT(ll.`name`) as language FROM e_school s 
				INNER JOIN e_school_languages lng ON lng.school_id = s.id
				INNER JOIN e_list_languages ll ON ll.id = lng.languages_id AND ll.lang = ".$_SESSION['lang_id']."
				GROUP BY s.id
				) lg ON lg.id = e_school.id			
			WHERE e_school.deleted = 0 AND e_school.disabled=0 ".$ageWhere;

			$group_name = 'category';
		}
		else{
			$sql = "SELECT e_school_lang.`name`, e_school_lang.school_type, e_school_lang.sity, e_school_lang.special, e_school_lang.age, e_school_lang.age2, e_school_lang.dates,  
			CASE WHEN e_school_lang.url = '' THEN e_school.id ELSE e_school_lang.url END as url, e_school.absolute_url, e_school.id, lg.language,
			e_school_category.category_id, e_country_lang.`name` as country_name, e_country_lang.country_id, e_school.image, e_school_lang.alt
			FROM e_school  
			INNER JOIN e_school_lang ON e_school_lang.school_id = e_school.id AND e_school_lang.lang = ".$_SESSION['lang_id']." ".
			$languageJoin.$businesJoin."
			INNER JOIN e_school_country ON e_school_country.school_id = e_school.id
			INNER JOIN e_country_lang ON e_country_lang.country_id = e_school_country.country_id AND e_country_lang.lang = ".$_SESSION['lang_id']."
			INNER JOIN e_school_category ON e_school_category.school_id = e_school.id
			LEFT JOIN (
				SELECT s.id, GROUP_CONCAT(ll.`name`) as language FROM e_school s 
				INNER JOIN e_school_languages lng ON lng.school_id = s.id
				INNER JOIN e_list_languages ll ON ll.id = lng.languages_id AND ll.lang = ".$_SESSION['lang_id']."
				GROUP BY s.id
				) lg ON lg.id = e_school.id			
			WHERE e_school.deleted = 0 AND e_school.disabled=0 ".$ageWhere;

			$group_name = 'country';
		}

		$list = $this->db->db_dataset_array($sql);
		
		$this->tpl->assign('data', $selected_params);
		$this->tpl->assign('language', $_SESSION['lang_folder']);	

                $sql = "SELECT title, text FROM pages WHERE id IN (31,32) AND lang = ".$_SESSION['lang_id'];
                $this->tpl->assign('page', $this->db->db_get_array($sql));

		if (count($list) == 0)
			$this->tpl->assign('failed', true);
		else
			$this->tpl->assign('tables', $this->getSchoolTables($list, $group_name));

		$this->Content = $this->fetchTemplate(CURRENT_TEMPLATE."modules/".$this->Params['template'], $this->tpl);
	}	
	
	
	//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	// формирование результата выборки школ по стране в разрезе категорий (программ)
	//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	// $list - результат select'a 
	public function getSchoolTables($list, $group, $paging)
	{
		$tpl = new  Template;
		$tpl->config_load($_SESSION['lang_folder']."/captions.cfg", 'school');
		$caption = $tpl->get_config_vars();

		$this->realModuleUrl = 'school';
		// корректировка полей
		for ($i=0; $i<count($list); $i++){
			// url
			$list[$i]['url'] = $this->createUrl($list[$i]['url'], $list[$i]['absolute_url'], '');
			// age
			if ($list[$i]['age'] > 0)
				$list[$i]['age'] = $caption['from']." ".$list[$i]['age'];
			if ($list[$i]['age2'] > 0)
				$list[$i]['age'] .= $caption['to']." ".$list[$i]['age2'];
		}
		
	// получим уникльный список категорий из таблицы школ
		$cats = get_uniq_by_col($list, $group.'_name', $group.'_id');
		for ($i=0; $i<count($cats); $i++){
			// получить значение id категории для текущей группы школ - для определения набора столбцов таблицы
			$category_id = dataset_select_value($list, 'category_id', $group.'_id', $cats[$i][$group.'_id'], 0); //выборка значения 'category_id' для поля $group.'_id' со значением $cats[$i][$group.'_id']
			// сформировать таблицы
			$tables[$i] = array('title' => $cats[$i][$group.'_name'], 
								'id' => $cats[$i][$group.'_id'], 
								'cols' => $this->getTableCols($category_id),
								'rows' => dataset_select($list, $group.'_id', $cats[$i][$group.'_id'])
								);
		}
		
		//view = plate / line
		$view = get_post_session_value('view', 'plate');
		
		$tpl->assign('paging', $paging);	
		$tpl->assign('view', $view);	
		$tpl->assign_by_ref('list', $tables);
		$tpl->assign('folder', $this->path_folder.$this->imgFolder);
		$tpl->assign('actionimgFolder', $this->path_folder . $this->actionimgFolder);
		$tpl->assign_by_ref('cols', $cols);
		$tpl->assign('col_count', count($cols));
		$tpl->assign('language', $_SESSION['lang_folder']);
		
		return $this->fetchTemplate(CURRENT_TEMPLATE.'modules/school_table.html', $tpl);
		
	}
	
	
	function getActionSchool($list, $group, $paging ){
		$tpl = new  Template;
		$tpl->config_load($_SESSION['lang_folder']."/captions.cfg", 'school');
		$caption = $tpl->get_config_vars();		
				
		// корректировка полей
		for ($i=0; $i<count($list); $i++){
			// url
			$url = $list[$i]['absolute_url']==1 ? $list[$i]['url'] : 'school/'.$list[$i]['url'];
			$list[$i]['url'] = $this->createUrl($url, $list[$i]['absolute_url'], '', false);
			// age
			if ($list[$i]['age'] > 0)
				$list[$i]['age'] = $caption['from']." ".$list[$i]['age'];
			if ($list[$i]['age2'] > 0)
				$list[$i]['age'] .= $caption['to']." ".$list[$i]['age2'];
				
			if ($list[$i]['action_from']) $list[$i]['action_from'] = YMDToDMY($list[$i]['action_from']);
			if ($list[$i]['action_to']) $list[$i]['action_to'] = YMDToDMY($list[$i]['action_to']);		
		}
		
		$group = 'category';
		// получим уникльный список категорий из таблицы школ
		$cats = get_uniq_by_col($list, $group.'_name', $group.'_id');

		for ($i=0; $i<count($cats); $i++){
			// получить значение id категории для текущей группы школ - для определения набора столбцов таблицы
			$category_id = dataset_select_value($list, 'category_id', $group.'_id', $cats[$i][$group.'_id'], 0); //выборка значения 'category_id' для поля $group.'_id' со значением $cats[$i][$group.'_id']
			// сформировать таблицы
			$sql = "SELECT f.`field`, f.`name` FROM e_school_fields f INNER JOIN e_category_school_fields csf ON csf.field = f.field AND csf.category_id = ".$category_id." WHERE f.lang=".$_SESSION['lang_id']." ORDER BY csf.ordno";
			$cols = $this->db->db_get_list($sql);			
			$tables[$i] = array('title' => $cats[$i][$group.'_name'], 
								'id' => $cats[$i][$group.'_id'], 
								'cols' => $cols,
								'rows' => dataset_select($list, $group.'_id', $cats[$i][$group.'_id'])
								);
		}

		$tpl->assign('paging', $paging);	
		$tpl->assign_by_ref('list', $tables);
		$tpl->assign('folder', $this->path_folder.$this->imgFolder);
		$tpl->assign('actionimgFolder', $this->path_folder . $this->actionimgFolder);
		$tpl->assign('language', $_SESSION['lang_folder']);

		return $this->fetchTemplate(CURRENT_TEMPLATE.'modules/school_action.html', $tpl);				
	}
	
	
	// поля таблицы	(порядок полей должен соответствовать порядку полей в таблице школы)
	private function getTableCols($category_id)
	{
		$sql = "SELECT f.`field`, f.`name` FROM e_school_fields f INNER JOIN e_category_school_fields csf ON csf.field = f.field AND csf.category_id = ".$category_id." WHERE f.lang=".$_SESSION['lang_id']." ORDER BY csf.ordno";
		return $this->db->db_get_list($sql);
	}
}
?>
