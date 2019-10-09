<?php
/* класс от которого наследуются все модули выводящие контент */ 
 abstract class Module
 {
	public $MetaTitle = "";
	public $MetaDescription = "";
	public $MetaKeywords = "";
	public $Content = "";
	public $CSSFile = "";
	public $Position = "CONTENT";
	public $Navigator = "";
	public $ContentImage = "";
	public $tpl = null;
	
	protected $Name = "";
	protected $Caption = "";
	protected $Params = null;
	protected $module_url = "";
	protected $method_url = "";
	protected $table = ""; // основная таблица
	protected $keyId = ""; // имя ключа в дополнительных таблицах
	protected $module_id = 0;
	protected $selected_action=array(0=>"-- Выберите действие --", "disable"=>"Отключить", "enable"=>" Включить", "deleting"=>" Удалить", "moving"=>" Переместить");
	protected $query = array();
	protected $last = 0; // индекст последней составляющей запроса
	
 	protected $db = null; 
	public $path_folder = '../upload/image/';
	
	public $mappedModuleUrl = ''; // ссылка заданная пользователем для модуля берется из файла mapping.cfg
	public $realModuleUrl = "";
	
	function __construct(&$db, &$params)
	{
		$this->db = $db;
		
		$this->Params = $params;

		if (isset($this->Params['url'])) $this->module_url = $this->Params['url']."/";
		if (isset($this->Params['position'])) $this->Position = $this->Params['position'];
		if (isset($this->Params['name'])) $this->Name = $this->Params['name'];
		if (isset($this->Params['caption'])) $this->Caption = $this->Params['caption'];

		if (isset($this->Params['css'])) $this->CSSFile = $this->Params['css'];

		if (isset($this->Params['meta_title']))	$this->MetaTitle = $this->Params['meta_title'];
		if (trim($this->MetaTitle) == "") $this->MetaTitle = $this->Caption;
		
		if (isset($this->Params['meta_description'])) $this->MetaDescription = $this->Params['meta_description'];
		if (trim($this->MetaDescription) == "") 
			if (isset($this->Params['description'])) 
				$this->MetaDescription = $this->Params['description'];
		
		if (isset($this->Params['meta_keywords'])) $this->MetaKeywords = $this->Params['meta_keywords'];
		
		if (isset($this->Params['image'])) $this->ContentImage = $this->Params['image'];

		$this->module_url = $this->Params['url'];
		$this->realModuleUrl = $this->Params['url'];
		$this->module_id = $this->Params['id'];
		
		$this->query = explode("/", $_SERVER['REQUEST_URI']);
		if ($this->query[count($this->query)-1] == "")	$this->last = count($this->query)-2;
		else $this->last = count($this->query)-1;
		
		//  реальный урл заданный в  файле mapping.cfg для этого модуля для языка по умолчанию
		$this->mappedModuleUrl = getMappedUrl($this->realModuleUrl);
		
	}
	
	// cформировать навигатор положения на сайте
	public function GetNavigator()
	{
		if ($this->mappedModuleUrl != '' && $this->module_url != $this->mappedModuleUrl && $_SESSION['lang_id'] == 0) // проверка url-ов по файлу соответствий  mapping.cfg
			$url = $this->mappedModuleUrl;
		else
			$url = langUrl($_SESSION['lang_url']).addEndSlash($this->module_url);
			
		$this->Navigator = ' <a href="'.$url.'">'.$this->Caption.'</a>';
	}

	// попытка вывода заполненого шаблона в переменную
	protected function fetchTemplate($templateName, &$tpl)
	{
		if (file_exists("./templates/".$templateName) == true) 
			return  $tpl->fetch($templateName);
		else 	
			return  "Не найден шаблон ".$templateName;
	}
	
		//возврат  404 ошибки
	protected function get404()
	{
		$Module = new E404("no_page");
		$Module->LoadContent();
		$this->Content = $Module->Content;	
	}	
	
	// получить id текущей школы
	public function getCurrentId()
	{
		if ($this->query[MODULEINDEX]!= $this->realModuleUrl ) return null;
		
		if (is_numeric($this->value))
			$where = "c.id = ".$this->value;
		else
			$where = "cl.url = '".$this->value."'";	

		return $this->db->db_get_value("SELECT c.id FROM ".$this->table." c INNER JOIN ".$this->table."_lang cl ON cl.".$this->keyId." = c.id  AND cl.lang = ".$_SESSION['lang_id']." WHERE ".$where);
	}
	

	// создание url 
	protected function createUrl($url, $absolute_url = 0, $parent_url = "", $enable_module_url = true)
	{
		$module_url ='';
		
		if ($absolute_url == 1) 
			$url = addEndSlash($url);
		else {
			if ($enable_module_url) 
				$module_url = $this->realModuleUrl.'/';
				
			$url = langUrl($_SESSION['lang_url']).$module_url.$parent_url.addEndSlash($url);
		}	
		return $url;
	}
	
	//++++++++++++++++++++++  FULLTEXT SEARCH  ++++++++++++++++++++
	// полнотектсовый поиск по текстам модуля
	public function searchText($searchString)
	{
		// выборка преобразование url в зависимости от типа
		$sql = "SELECT t.*, l.*, p.title, SUBSTRING(p.`text`, 1, 255) as description, l.url, t.absolute_url, MATCH(p.title, p.text) AGAINST('".$searchString."') as rang
		FROM ".$this->table."_pages p
		INNER JOIN ".$this->table." t ON t.id = p.".$this->keyId." AND t.deleted = 0 AND t.disabled = 0
		INNER JOIN ".$this->table."_lang l ON l.".$this->keyId." = t.id AND l.lang = ".$_SESSION['lang_id'].
		" WHERE MATCH(p.title, p.text) AGAINST('".$searchString."') AND p.lang = ".$_SESSION['lang_id'];

		$list = $this->db->db_dataset_array($sql);

		if (count($list) ==0 ) $list = array();

		for ($i=0; $i<count($list); $i++) {
			if  (trim($list[$i]['title']) == '') $list[$i]['title'] = $list[$i]['name'];
			$list[$i]['description'] = strip_tags(stripslashes($list[$i]['description'])).'...';
			$list[$i]['url'] = $this->createUrl($list[$i]['url'], $list[$i]['absolute_url']);
		}
		
		return $list;
	}
	

	// загрузка изображения
	protected function loadImage(&$data, $w, $h)
	{
		// загрузка и преобразование рисунка
		if ($_FILES['image']['name'] != '')	{
		
			$fileName = upload_file($_FILES, $this->path_folder.$this->imgFolder, 'image');

			$image = new ImgConvert('resize', $w, $h);
	
			rename($this->path_folder.$this->imgFolder.$fileName, $this->path_folder.'temp/'.$fileName);
			
			$image->convert($this->path_folder.'temp/'.$fileName);
			$image->saveImage($this->path_folder.$this->imgFolder.$fileName);
			
			unlink($this->path_folder.'temp/'.$fileName);
		}
		else {
			if ( isset($data['listimage']) && ($data['listimage']!='') ) 
				$fileName = $data['listimage'];
			else {
				if ( isset($data['delete_image']) ){
					unlink($this->path_folder.$this->imgFolder.$data['oldimage']);
					$fileName = '';
				}	
				else	
					$fileName = $data['oldimage'];
			}		
		}		
		return $fileName;		
	}
	
//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++		
//---------------------------   admin  ------------------------------------------
//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++	

		// получить навигатор позиции 
	protected function AdminNavigator()
	{
		$this->Navigator = '<a href="'.$this->module_url.$this->method_url.'">'.$this->Caption.'</a>';	
	}
	
	
	public function NewItem(&$params)
	{
		$params['action'] = 'insert';
		$params['action_title'] = 'Создать ';
			
		$this->EditItem($params);
	}
	
	// --- del 
	public function Del(&$params)
	{
		$sql = "UPDATE ".$this->table." SET deleted = 1 WHERE id = ".$params['pId'];
		$this->db->db_query($sql);
		
		$this->flush_cache();
	}	
	
	// удалить из кеша файл с каталогом товаров
	protected function flush_cache()
	{
		$files = glob("../cache/*".$this->table."*.*", GLOB_NOSORT);
		if (is_array($filescount) && $files>0)
			foreach ($files as $f)
				unlink($f);
	}

	// корректировка URL
	protected function correctUrl(&$data)
	{
		if (trim($data['url']) == '')
			$url = createUrl(utf2str($data['name'], "w"), 0, '');
		else	
			$url = createUrl($data['url'], 0, '');
		
		if (isset($data['action']) && $data['action'] == 'insert')
			$url = $this->db->avoidDuplication($this->table.'_lang', 'url', $url, ' AND lang = "'.$_SESSION['lang_id'].'"', $data['id']);
		
		return $url;
	}
	
	// вставка ссылки в таблицу references 	
	protected function insertReference($url, $module_id)
	{
		$sql = "SELECT id FROM `references` WHERE url ='".$url."'";
		$id = $this->db->db_get_value($sql);

		if (  $id == null ) 
			$sql = "INSERT INTO `references` (`url`, `module_id`, `lang`) VALUES ('".$url."', ".$module_id.", ".$_SESSION['lang_id'].")";
		else
			$sql = "UPDATE `references` SET module_id = ".$module_id.", lang = ".$_SESSION['lang_id']." WHERE id = ".$id;

		$this->db->db_query($sql);

	}
	
	
	// метод вызываемый при submit - e формы, в массиве post переменная action хранит имя  обработчкика данных
	public function submit(&$params)
	{
		if (isset($_POST['action']))
		{	
			$action = $_POST['action'];
			$this->$action($_POST);
		}
	}
		
	// установка поля disabled отключение 
	public function Switche(&$params)
	{
		$sql = "UPDATE ".$this->table." SET disabled = CASE WHEN disabled = 1 THEN 0 ELSE 1 END WHERE id = ".$params['id'];
		$this->db->db_query($sql);
		
		$this->flush_cache();
	}	
	
	protected function loadLangList()
	{
		include './cache/lang_list.php';
		return $list;
	}

//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
// ++++++++++++++++    связи со статьями для стран	
//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	public function getBindedArticles(&$data)
	{
		$sql = "SELECT a.id, a.page_id, a.parent, a.`name` FROM ".$this->table."_articles cp 
			INNER JOIN articles a ON a.page_id = cp.page_id AND a.deleted = 0 AND a.disabled = 0
		WHERE cp.".$this->keyId." = ".$data['id']." AND cp.lang = ".$_SESSION['lang_id'];

		$list = $this->db->db_dataset_array($sql);
	
		$content = 	"<table>";
		for ($i=0; $i<count($list); $i++) {
			$content .= 
			"<tr><td>".$list[$i]['name']."</td>
			<td>&nbsp;&nbsp;<a href='".HOST_ADMIN."?module=Articles&method=EditItem&ispage=1&pId=".$list[$i]['parent']."&itemId=".$list[$i]['id']."'><img src='./admin/templates/images/edit.gif' alt='Редактировать' title='Редактировать'></a>&nbsp;&nbsp;&nbsp;
			<a href='javascript:void(0);' onclick='del_binded_articles(\"ajax.php?module=".$this->Name."&method=articleDel&field=".$this->keyId."&".$this->keyId."=".$data['id']."&page_id=".$list[$i]['page_id']."\");'><img src='./admin/templates/images/delete.gif' alt='Удалить' title='Удалить'></a></td>
			</tr>";
		}	
		$content .="</table>";
		
		return $content;
	}
	
	/**** привязка статей *****/
	
	// форма для отображения списка статей
	public function getCategorysListForm(&$params)
	{
		$list = "";
		includeModule('Articles');
		$p = null;
		$article = new Articles($this->db, $p);
		$article->getCategory(0, 0, $list);
		unset($article);
		
		echo "<div id='formHead'><table width='100%'><tr><td>Выбрать cуществующую статью</td><td><a href='javascript:void(0);' onclick=\"$('#editForm').css('display', 'none');\">X</a></td></tr></table></div>
		<div id='formBody'><span class='small'>Для <b>создания новой</b> статьи воспользуйтесь разделом 'Структура сайта' => 'Статьи'</span><br/><br/>
		<SELECT name='category_id' onchange=\"$('#list').load('".HOST."ajax.php?module=".$this->Name."&method=getArticlesList&field=".$params['field']."&".$params['field']."=".$params['value']."&article_id='+this.value);\">
		<OPTION value=\"\">-- Выберите категорию --</OPTION>
		<OPTION value=\"0\">".$this->Caption."</OPTION>"
		.$list.
		"\n</SELECT>
		<div id='list'>
		</div>";
	}
	
	// список статей выбранной категории
	public function getArticlesList(&$params)
	{
		includeModule('Articles');
		$p = null;
		$article = new Articles($this->db, $p);
		$list = $article->GetItems((int)$params['article_id']);
		
		if (count($list)>0){
			echo "<ul class='list'>";
			foreach ($list as $item)
				if ($item['page_id'] != "" && $item['disabled'] == 0)
					echo '<li><a href="javascript:void(0);" onclick="$(\'#binded_articles\').load(\'ajax.php?module='.$this->Name.'&method=setArticle&field='.$params['field'].'&'.$params['field'].'='.$params[$params['field']].'&page_id='.$item['page_id'].'\');">'.$item['name'].'</a></li>';
			echo "</ul>";
		}
		unset($article);
	}
	
	// установить статью для выбраной страны
	public function setArticle(&$params)
	{
		$sql = "SELECT COUNT(*) FROM ".$this->table." WHERE ".$params['field']." = ".(int)$params[$params['field']]." AND lang = ".$_SESSION['lang_id']." AND page_id = ".(int)$params['page_id'];
	
		if ( $this->db->db_get_value($sql) == 0 ) {
			$sql = "INSERT INTO ".$this->table."_articles (".$params['field'].", lang, page_id) VALUES (".(int)$params[$params['field']].", ".$_SESSION['lang_id'].", ".(int)$params['page_id'].")";
			$this->db->db_query($sql);
		}
		
		$data = array('id'=>(int)$params[$params['field']]);
		return $this->getBindedArticles($data);
	}
	
	// удаление статьи из связи 
	public function articleDel(&$params)
	{
		$sql = "DELETE FROM ".$this->table."_articles WHERE ".$params['field']." = ".(int)$params[$params['field']]." AND page_id = ".(int)$params['page_id']." AND lang = ".$_SESSION['lang_id'];
		$this->db->db_query($sql);

		$data = array('id'=>(int)$params[$params['field']]);
		return $this->getBindedArticles($data);
	}
	
	
 }
?>