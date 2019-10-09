<?php
class Action extends Module {
	
	function __construct(&$db, &$params)
	{
		parent::__construct($db, $params);		
		if ($this->realModuleUrl == "") $this->realModuleUrl = 'action';
		define('DIR_IMG_PROGRMM', '/upload/image/programm/');
	}
	
	public function LoadBoxContent(){	}

	public function LoadContent()
	{
		$this->MetaTitle = $this->Params['caption'] .' - '. META_TITLE;
		//$this->MetaKeywords = $page['meta_keywords'];
		//$this->MetaDescription = $page['meta_description'];
		
		includeModule('School');
	    $p = array('Name'=>'School');
	    $school = new School($this->db, $p);         
		$school_table = $school->getActionSchool();

		$this->tpl->assign_by_ref('school', $school_table);
		$this->tpl->assign('lang_url', langUrl($_SESSION['lang_url']));
		$this->tpl->assign('language', $_SESSION['lang_folder']);
		$this->tpl->assign('dir_img', DIR_IMG_PROGRMM);		
		

		$this->Navigator = '<span class="nav"></span><a href="/">Главная</a> > Акции';			
		
		$_SESSION['last_programm_breadcrumb'] = '<span class="nav"></span><a href="/">Главная</a> > <a href="/action.html">Акции</a>';

		$this->Content = $this->fetchTemplate(CURRENT_TEMPLATE."modules/".$this->Params['template'], $this->tpl);
	}
	
}
