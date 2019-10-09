<?php
class Box 
{
	public $Position = "";
	public $CSSFile = "";
	public $tpl = null;
	public $tplName = "";

	private $db = null;	
	private $Params = null;
	public $Name = "";
	private $Caption = "";

    /*Создание бокса*/
	function __construct(&$db, &$params)
	{
		$this->db = $db;
		
		$this->Params = $params;
		
		if (isset($this->Params['position'])) $this->Position = $this->Params['position'];
		if (isset($this->Params['name'])) $this->Name = $this->Params['name'];
		if (isset($this->Params['caption'])) $this->Caption = $this->Params['caption'];
		if (isset($this->Params['css'])) $this->CSSFile = $this->Params['css'];
		if (isset($this->Params['template'])) $this->tplName = $this->Params['template'];	
	}
  
    //загрузка модуля формирующего бокс
	public function LoadBoxContent()
	{
		if ($this->Params['text'] != null && trim($this->Params['text']) != "")
			$this->Content .= $this->GetText($this->Params['text']);
		else
			if ($this->Params['name'] != null)
					$this->Content = $this->GetBoxContent($this->Params['name']);
		return $this;	
	}
  
 
  // выполнение  заданого для текущего бокса php - модуля
  private function GetBoxContent($module)
  {
 	if (file_exists(DIR_MODULES.$module.".php") == true)
	{	
		if (class_exists($module) == false)
		{
			include DIR_MODULES.$module.".php";
		}
	}
	else
		$content = "Не найден модуль ".DIR_MODULES.$module.".php";	

	if (class_exists($module) == true)
	{
		$obj = new $module($this->db, $this->Params);
		$obj->tpl = &$this->tpl;
		$method = $this->Params['method'];
		$obj->$method();

		if ($obj->tplName != '') $this->tplName = $obj->tplName;
		if ($obj->CSSFile != '') $this->CSSFile = $obj->CSSFile;
		
		$this->tpl->assign('CURRENT_TEMPLATE', CURRENT_TEMPLATE);
				
		if (file_exists("./templates/".CURRENT_TEMPLATE."box/".$this->tplName) == true) 
			$content = $this->tpl->fetch(CURRENT_TEMPLATE."box/".$this->tplName);
		else 	
			$content = "Не найден шаблон ".CURRENT_TEMPLATE."box/".$this->tplName;
	}
	else
		$content = "Не найден класс ".$module;
	
	return $content;
  }


  // отображение текста в контенте бокса
  private function GetText($text)
  {
	$this->tpl->assign('text',stripslashes($text));
	
	if (file_exists("./templates/".CURRENT_TEMPLATE."box/".$this->tplName) == true)
		return $this->tpl->fetch(CURRENT_TEMPLATE."box/".$this->tplName);
	else 
		return "Не найден шаблон ".CURRENT_TEMPLATE."box/".$this->tplName;
  }	
}
?>
