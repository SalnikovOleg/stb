<?php
class Document
{
	//Meta теги изначально определяется из параметров сайта
	//может переопределятся модулем или страницей
	public $MetaTitle  = '';
	public $MetaDescription = '';
	public $MetaKeywords = '';
	//id типа документа
	protected $documentId = 0;
	protected $document = array();
	// каталог текущего шаблона сайта
	public $CurrentTemplate = '';
	public $StyleSheet = '';
	//Smarty шаблон
	public $Template = null;
	protected $TemplateClassName = "";
	// массив разделов страницы ключами являются смарти переменные, значениями html текст блока полученый в результате обработки шаблона
	public $Boxes = null;
	//объект базы данных
	protected $db = null;
	
	protected $Navigator = "";
	private $ContentImage = "";
	
    function __construct(&$db, $documentId)
	{
		$this->db = $db;
		
		// id документа определяющего набор боксов
		$this->documentId = $documentId;
		
		define('CURRENT_TEMPLATE', MAIN_TEMPLATE."/");
		
		// метатеги и шаблон по умолчанию
		$this->MetaTitle = META_TITLE;
		$this->MetaDescription = META_DESCRIPTION;
		$this->MetaKeywords = META_KEYWORDS;
		
		$this->getDocDesign();
		
		$this->CurrentTemplate = CURRENT_TEMPLATE.$this->document[$this->documentId]['template'];
		if (isset($this->document[$this->documentId]['css']) && trim($this->document[$this->documentId]['css']) !="")
			$this->StyleSheet = $this->document[$this->documentId]['css'];
		//else 	
			//$this->StyleSheet = "index.css";
		
		$this->TemplateClassName = "Template"; // имя класса шаблонов smarty: для сайта - Template ; для админа - AdminTemplate
		$this->Template = new $this->TemplateClassName;
	}

	// получение данных html документа имя шаблона и имя css файла
	private function getDocDesign()
	{
		$fileName = './cache/html_doc_conf.php';
		if (file_exists($fileName) == false)
		{	
			$list = $this->db->db_dataset_array("SELECT id, template, css FROM a_documents");	
			$fileContent = "<?php\n";
			for ($i=0; $i<count($list); $i++)
				$fileContent .= '$this->document['.$list[$i]['id'].'] = array("template"=>"'.$list[$i]['template'].'", "css"=>"'.$list[$i]['css'].'");'."\n";
			$fileContent .="?>";
			write_to_file($fileName, $fileContent);
		}	
		include ($fileName);
	}
	
	/*загрузка массива  боксов*/
	public function LoadBoxes()
	{
		$query = explode("/",$_SERVER['REQUEST_URI']);

		if (!isset($query[MODULEINDEX])) $module_link = '';
		else $module_link = $query[MODULEINDEX];		
		
		$dataset = $this->db->get_all_boxes($this->documentId, trim($module_link)); // выбрать все блоки для данного документа

		if ($dataset == null) return;

		foreach ($dataset as $row)
		{
			$Box = new Box($this->db, $row);
			$Box->tpl = new $this->TemplateClassName;
			$this->Boxes[] = $Box->LoadBoxContent();
		}
		
		unset($dataset);
	}

	// загрузить массив боксов в шаблон
	public function AssignBoxes()
	{
		$this->loadPositionList();

		foreach($this->Boxes as $Box){
			if (!isset($positions[$Box->Position]))
				$positions[$Box->Position] = $Box->Content;
			else		
				$positions[$Box->Position] .=  $Box->Content;
		}

		foreach($positions as $pos => $content)
			$this->Template->assign($pos, $content);
		
		$this->Template->assign('NAVIGATOR', $this->Navigator);
		$this->Template->assign('language', $_SESSION['lang_folder']);
		$this->Template->assign('ADDRESS', ADDRESS);
		$this->Template->assign('COPYRIGHT', COPYRIGHT);
		$this->Template->assign('EMAIL', EMAIL);
		$this->Template->assign('ADMIN_EMAIL', ADMIN_EMAIL);
		$this->Template->assign('PHONE1', PHONE1);
		$this->Template->assign('CONTENT_IMAGE', $this->ContentImage);		
		$this->Template->assign('MAINPAGE', HOST.langUrl($_SESSION['lang_url']));
		$this->Template->assign('SITE_NAME', META_TITLE);
		
		unset($position);
	}
	
	// создание массива позиций
	private function loadPositionList()
	{
		$fileName = './cache/box_position.php';
		if (file_exists($fileName) == false)
		{	
			$list = $this->db->db_dataset_array('SELECT `code`, "" as content FROM '.T_POSITIONS, 'code', 'content');	
			$fileContent = "<?php\n";
			for ($i=0; $i<count($list); $i++)
				$fileContent .= '$positions["'.$list[$i]['code'].'"] = "";';
			$fileContent .="\n?>";
			write_to_file($fileName, $fileContent);
		}	
		include ($fileName);	
	}
	
	
	private function getModuleData()
	{
		$query = explode("/", $_SERVER['REQUEST_URI']);

		if (isset($_SERVER['SEARCHED_URL']) && trim($_SERVER['SEARCHED_URL']) !='' ){
			// попыкта найти ссылку в таблице references если вдруг была задана абсолютная ссылка";
			$module = $this->db->getModuleFromReferences($_SERVER['SEARCHED_URL'], $this->documentId);

			if ($module != null){
				return $module;
			}
		}	

		if (count($query) == MODULEINDEX  && trim($query[MODULEINDEX-1]) != '') return null;

		if (!isset($query[MODULEINDEX])) $module_link = '';
			else $module_link = $query[MODULEINDEX];
	
	// для обработки урл созданых движком 
		if (strpos($module_link,".".DEFAULT_EXT) != false || trim($module_link) =="")
		{
		  // если ссылка типа  host/pag.html или host/  то ищем модуль по таблице references по url";
			$module = $this->db->getModuleFromReferences($module_link, $this->documentId);
			// если не нашли то ищем по таблице модулья по url
			if ($module == null){
				$module = $this->db->get_module($module_link, $this->documentId);
			}
		}
		else
		{  // если ссылка типа host/modulename/  то стразу ищем по модулю"; 
			$module = $this->db->get_module($module_link, $this->documentId);
			//если не нашли попробуем найти по таблице references 
			if ($module == null)
				$module = $this->db->getModuleFromReferences($module_link, $this->documentId); 	
		}

		return $module;
	}
	
	/* получить запрошенную страницу */
	public function LoadModule()
	{
	global $result; // определяется в index.php возвращается Language->SelectLanguage();
	if ($result == '404') 
		$module == null;
	else	
	{
			// проверка 404
		if ( check404($_SESSION['REQUEST_URI']) == false )	{
 			 //попытка выбрать подуль возможно со страницей;
			$module = $this->getModuleData();

			// подлючение файла с описанием модуля
			includeModule($module['name']);
		}
                else
			$module = null;
		
	  }	
		// создание объекта модуля			
		if ($module == null) 	
			$Module = new E404("no_page");
		else if (class_exists($module['name']) == false) 
				$Module = new E404("no_class");
			else 
				$Module = new $module['name']($this->db, $module);
		
		$Module->tpl = new $this->TemplateClassName;
		
		// выполнение методов переданных из HTML форм методом POST
		// массив POST должен содержать елемент action  в котором  хранится имя выполняемого метода
		if (isset($_POST['action']))
		{	
			$action = $_POST['action'];
			$Module->$action($_POST);
		}
		
		$Module->LoadContent();	
		
		$this->Navigator = $Module->Navigator;
		$this->ContentImage = $Module->ContentImage;
		
		//метатеги определенные в модуле
		if (isset($Module->MainTemplate)) 
			$this->CurrentTemplate = CURRENT_TEMPLATE."/".$Module->Template;
		if (isset($Module->MetaTitle)) 
			$this->MetaTitle  = $Module->MetaTitle;
		if (isset($Module->MetaDescription)) 
			$this->MetaDescription = $Module->MetaDescription;
		if (isset($Module->MetaKeywords)) 
			$this->MetaKeywords = $Module->MetaKeywords;

		$this->Boxes[] = $Module;
		
		unset($Module, $module);
	}

	// отображение контента
	public function Display()
	{
		if (file_exists("./templates/".$this->CurrentTemplate) == true) 
			$this->Template->display($this->CurrentTemplate); 
		else 	
			echo "Не найден шаблон ".$this->CurrentTemplate;
	}
	
	
	//вывод подключения сss в хедере файла
	public function linkStyleSheet()
	{
		$linked = array($this->StyleSheet);
		foreach ($this->Boxes as $Box)
			if ($Box->CSSFile !=null && in_array($Box->CSSFile, $linked) == false )
			{
				$linked[] = $Box->CSSFile;
				echo '<link rel="stylesheet" type="text/css" href="//'.HOST_NAME.'/templates/'.CURRENT_TEMPLATE.'css/'.$Box->CSSFile.'" />'."\n";
			}
	}
}	
?>
