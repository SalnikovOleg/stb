<?php
class Admin extends Document
{
	function __construct(&$db, $documentId)
	{
		$this->db = $db;
		
		// id документа определяющего набор боксов
		$this->documentId = $documentId;

		define('CURRENT_TEMPLATE', "");
		
		$this->MetaTitle = META_TITLE;
		$this->CurrentTemplate = "index.html";
		
		$this->TemplateClassName = "AdminTemplate";
		
		$this->Template = new $this->TemplateClassName;
	}
	
	public function LoadModule($moduleName)
	{
		if ($moduleName == "") return "";
		
		$module = $this->db->get_moduleByName($moduleName, $this->documentId); 
		if ($module == null) 	
			$module['name'] = $moduleName;
			
		// подлючение файла с описанием модуля
		if (class_exists($module['name']) == false && file_exists(DIR_MODULES.$module['name'].".php") !== false)
		{	
			include DIR_MODULES.$module['name'].".php";
		}
		
		// создание объекта модуля			
		if ($module == null) 	
			$Module = new E404("no_page");
		else if (class_exists($module['name']) == false) 
				$Module = new E404("no_class");
			else 
				$Module = new $module['name']($this->db, $module);
		
		if ( isset($_GET['method']) ) $method = $_GET['method'];
		else $method = 'ItemsList';
		
		$Module->tpl = new $this->TemplateClassName;
		
		// выполнение методов переданных из HTML форм
		if (isset($_POST['action']))
		{	
			$action = $_POST['action'];
			$Module->$action($_POST);
		}
		
		$Module->$method($_GET);  
		
		$this->Navigator = $Module->Navigator;
				
		$this->Boxes[] = $Module;
		
		unset($Module, $module);
	}
}
?>