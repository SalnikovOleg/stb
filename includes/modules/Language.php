<?php
class Language extends Module
{
	function __construct(&$db, $params)
	{
		parent::__construct($db, $params);
		$this->table = T_LANGUAGE;
	}

	public function SetDefaultLanguage()
	{
		if (!isset($_SESSION['lang_id']))
		{
			$_SESSION['lang_id'] = 0;
			$_SESSION['lang_url'] = '';
			$_SESSION['lang_folder'] = 'russian';
		}
	}
	
	public function SelectLanguage()
	{
		if ( MULTILANGUAGE )
		{	
			if (isset($_POST['lang_id'])) //если переключили язык
			{	
				if ($this->ChangeLanguage())
				  if ($_SESSION['lang_url'] == 'ru')
                                         header("Location:".HOST);
                                   else
					header("Location:".HOST.langUrl($_SESSION['lang_url']));
			}
			else {	// проверям не сменили ли язык в адресной строке
				if ( trim($this->Params[1]) =="" ) $this->Params[1] = 'ru';
			        if (in_array($this->Params[1], array('', 'ru', 'ua', 'en')) === false){
					return '404';	
				}
				
				if ( !isset($_SESSION['lang_url']) || ($this->Params[1] != $_SESSION['lang_url'])  )
					$this->LoadLanguage($this->Params[1]);
			}
		}
		else
			$this->LoadLanguage($this->Params[1]); // загрузка языка по умолчанию
			
	}
	
	// изменения языка через $_POST
	public function ChangeLanguage()
	{
		if (isset($_POST['lang_id']) && $_POST['lang_id'] != $_SESSION['lang_id'] )
		{
			// удаление сохраненных в сессии для предыдущего языка деревьев статей
			unset($_SESSION['articles'], $_SESSION['news'], $_SESSION['faq'], $_SESSION['services'], $_SESSION['a_params'], $_SESSION['product_catalogue']);
			
			$sql = 'SELECT * FROM '.$this->table.' WHERE `id` ='.$_POST['lang_id'];
			$this->SetSessionLang($this->db->db_get_array($sql));
			return true;
		}
		else
		{
			$this->LoadLanguage("");
			return false;
		}
	}
	
	public function LoadContent() {	}
	
	public function LoadBoxContent()
	{	
		$fileName = './cache/lang_list.php';
		if (file_exists($fileName) == false)
		{	
			$list = $this->db->db_dataset_array('SELECT * FROM '.T_LANGUAGE.' WHERE deleted = 0');
			$fileContent = "<?php\n";
			for ($i=0; $i<count($list); $i++){
				if ($list[$i]['url'] == 'ru' ) $list[$i]['url'] ='';
				else $list[$i]['url'] .='/';
				$fileContent .= '$list['.$list[$i]['id'].'] = array("id"=>'.$list[$i]['id'].', "name"=>"'.$list[$i]['name'].'", "image"=>"'.$list[$i]['image'].'", "url"=>"'.$list[$i]['url'].'");'."\n";
			}
			$fileContent .="?>";
			write_to_file($fileName, $fileContent);
		}	
		include ($fileName);
	
		$current =  array ('id'=>$_SESSION['lang_id'], 'name'=>$_SESSION['lang_name'], 'image'=>$_SESSION['lang_image'], 'lang_url'=>$_SESSION['lang_url']);
		$this->tpl->assign_by_ref('current', $current);
		$this->tpl->assign_by_ref('list', $list);
		$this->tpl->assign('CURRENT_TEMPLATE', CURRENT_TEMPLATE);
		$this->tpl->assign('url', HOST.substr($_SERVER['REQUEST_URI'],1));
	}
	

	private function LoadLanguage($url)
	{
		$sql = 'SELECT * FROM '.$this->table.' WHERE `url` ="'.$url.'"';
		$data = $this->db->db_get_array($sql);
			
		if ($data == null && !isset($_SESSION['lang_id']))
		{	
			$sql = 'SELECT * FROM '.$this->table.' WHERE id = '.DEFAULT_LANGUAGE;
			$data = $this->db->db_get_array($sql);
			$this->SetSessionLang($data);
		}
		else if ($data != null)
			$this->SetSessionLang($data);
	}

	
	private function SetSessionLang(&$data)
	{
		$_SESSION['lang_id'] = $data['id'];
		if ( MULTILANGUAGE )
			$_SESSION['lang_url'] = trim($data['url']);
		else
			$_SESSION['lang_url'] = '';
		$_SESSION['lang_folder'] = trim($data['folder']);
		$_SESSION['lang_name'] = $data['name'];
		$_SESSION['lang_image'] = $data['image'];
	}
}
?>