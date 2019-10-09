<?php
class Search extends Module
{
	private $resultList = array();
	private $search = array();
	
	function __construct(&$db, &$params)
	{
		parent::__construct($db, $params);
		$this->module_url = "search/";
	}

	public function LoadBoxContent()
	{
		$this->tpl->assign('url', HOST.langUrl($_SESSION['lang_url']).$this->module_url);
		$this->tpl->assign('CURRENT_TEMPLATE', CURRENT_TEMPLATE);
		
		$this->tpl->config_load($_SESSION['lang_folder']."/captions.cfg", 'search');
		$caption = $this->tpl->get_config_vars();
		$this->tpl->assign('lang_search_data', 'var default_search = "'.$caption['search'].'"; var empty_message = "'.$caption['search_emty'].'";');
	}
	
	public function LoadContent()
	{ 
	    $this->tpl = new Template;
		
		if (!isset($_POST['search_string'])) return;
		
		$this->resultList = $this->search('Country', $_POST['search_string']);
		$this->resultList = array_merge($this->resultList, $this->search('Category', $_POST['search_string']));
		$this->resultList = array_merge($this->resultList, $this->search('School', $_POST['search_string']));
		$this->resultList = array_merge($this->resultList, $this->search('Articles', $_POST['search_string']));
		
		$this->tpl->assign('search_string', $_POST['search_string']);
		$this->tpl->assign('list', $this->resultList);
		$this->tpl->assign('url', HOST.$_SESSION['lang_url']);
		
		$this->Content = $this->fetchTemplate(CURRENT_TEMPLATE."modules/".$this->Params['template'], $this->tpl);
	}
	
	// вызов метода поиска в модуле
	private function search($module, $searchString)
	{
		includeModule($module);
		$p = array('Name'=>$module);
		$obj = new $module($this->db, $p);
		return  $obj->searchText($searchString);
	}
}
?>