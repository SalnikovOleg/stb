<?php
class AdminMenu extends Module
{
	private $tree = "";
	
	function __construct(&$db, $params)
	{
		parent::__construct($db, $params);
	}
	
	public function LoadBoxContent()
	{/*
		$file = './cache/'.$_SESSION['lang_url'].'_adminmenu.html';
		
		if (file_exists($file) != false)
			$this->tree = file_get_contents($file);
		else
		{
		*/
			$this->CreateMenu();
			//write_to_file($file, $this->tree);
		//}

		$this->CreateMenu();
		$this->tpl->assign_by_ref('list', $this->tree);
	}

	public function LoadContent() {}
	
	// сформировать список меню
	private function CreateMenu()
	{
		$this->tpl->config_load($_SESSION['lang_folder']."/admin.cfg", 'mainmenu');
		$caption = $this->tpl->get_config_vars();
	
		includeModule('Editor');
		$p = array('Name', 'Editor');
		$c = new Editor($this->db, $p);
		$editorMenu = $c->loadMenu();
		unset($c);
		
		$this->tree = '
<ul class="dropdown">
<li><a href="javascript:void(0);">'.$caption['structure'].'</a>
	<ul>
		<li><a href="'.HOST.'admin/index.php?module=Articles&method=ItemsList">'.$caption['articles'].'</a>
		<li><a href="'.HOST.'admin/index.php?module=News&method=ItemsList">'.$caption['news'].'</a>
		'.($this->check_role('mainmenu')?'<li><a href="'.HOST.'admin/index.php?module=MainMenu&method=ItemsList">'.$caption['mainmenu'].'</a>':'').'
		'.($this->check_role('texts')?'<li><a href="'.HOST.'admin/index.php?module=Pages&method=ItemsList">'.$caption['texts'].'</a>':'').'
		'.($this->check_role('bloks')?'<li><a href="'.HOST.'admin/index.php?module=Boxes&method=ItemsList">'.$caption['bloks'].'</a>':'').'
		'.($this->check_role('reftable')?'<li><a href="'.HOST.'admin/index.php?module=AdminMenu&method=viewReferenceTable">'.$caption['reftable'].'</a>':'').'
		'.($this->check_role('footer_hrefs')?'<li><a href="'.HOST.'admin/index.php?module=Footer_hrefs&method=ItemsList">'.$caption['footer_hrefs'].'</a>':'').'
		<!--<li><a href="'.HOST.'admin/index.php?module=Modules&method=ItemsList">Разделы сайта (модули)</a>-->
	</ul>
</li>

<li><a href="javascript:void(0);">'.$caption['catalogue'].'</a>
	<ul>
		<li><a href="'.HOST.'admin/index.php?module=Country&method=ItemsList">'.$caption['countrys'].'</a></li>
		<li><a href="'.HOST.'admin/index.php?module=School&method=ItemsList">'.$caption['school'].'</a></li>
		<li><a href="'.HOST.'admin/index.php?module=Category&method=ItemsList">'.$caption['programs'].'</a></li>
		<li><a href="'.HOST.'admin/index.php?module=Programm&method=ItemsList">'.$caption['programscountry'].'</a></li>
		<li><a href="'.HOST.'admin/index.php?module=Gallery&method=ItemsList">'.$caption['gallery'].'</a></li>
		<li><a href="'.HOST.'admin/index.php?module=Video&method=ItemsList">'.$caption['video'].'</a></li>
	</ul>
</li>

<li><a href="javascript:void(0);">'.$caption['lists'].'</a>
	<ul>
		<li><a href="'.HOST.'admin/index.php?module=Dictionary&method=ItemsList&table='.T_LIST_SCHOOLTYPE.'">'.$caption['e_list_schooltype'].'</a></li>
		<li><a href="'.HOST.'admin/index.php?module=Dictionary&method=ItemsList&table='.T_LIST_BUSINESS.'">'.$caption['e_list_business'].'</a></li>
		<li><a href="'.HOST.'admin/index.php?module=Dictionary&method=ItemsList&table='.T_LIST_LANGUAGES.'">'.$caption['e_list_languages'].'</a></li>
	</ul>
</li>
'.($this->check_role('settings')?'
<li><a href="javascript:void(0);">'.$caption['settings'].'</a>
	<ul>
		<li><a href="'.HOST.'admin/index.php?module=Params&method=ItemsList">'.$caption['parameters'].'</a>
		<li><a href="'.HOST.'admin/index.php?module=Users&method=ItemsList">'.$caption['users'].'</a>
		<li><a href="javascript:void(0);" onclick="clearcache();">'.$caption['clearcache'].'</a>
		<li><a href="javascript:void(0);" onclick="optimize();">'.$caption['optimize'].'</a>
	</ul>	
</li>	
':'').'

'.($this->check_role('design')?'
<li><a href="javascript:void(0);">'.$caption['design'].'</a>
	'.$editorMenu.'
</li>	
':'').'

<li><a href="javascript:void(0);">Обратная связь</a>
	<ul>
		<li><a href="'.HOST.'admin/index.php?module=Subscribe&method=get_all">Подписчки. Все</a>
		<li><a href="'.HOST.'admin/index.php?module=Subscribe&method=get_new">Подписчки. Новые</a>
		<li><a href="'.HOST.'admin/index.php?module=Message&method=ItemsList">Email</a>
	</ul>	
</li>	


<li><a href="'.HOST.'admin/index.php?logout=1">'.$caption['exit'].'</a>	
</li>	

</ul>
<script type="text/javascript">
	function optimize(){ if (confirm(\'Оптимизацию имеет смысл проводить после вставки или удаления большого числа данных! Продолжить?\')) $.get(\'admin/ajax.php?module=AdminMenu&method=OptimizeTable\', {}, function(){alert(\'Оптимизация выполнена!\');}); }
	function clearcache(){ $.get(\'admin/ajax.php?module=AdminMenu&method=ClearCache\', {}, function(){alert(\'Файлы кеша очищены!\');}); }
</script>
';
	$this->tpl->clear_config();
	}

	private function check_role($item)
	{
	   $deny_list=array('mainmenu', 'texts', 'bloks', 'reftable', 'settings', 'design', 'footer_hrefs');
	
	  if ($_SESSION['RoleId'] == 1) return true;
	  
	  if ($_SESSION['RoleId'] == 4)
	    if (in_array($item, $deny_list))
	      return false;
	    else
	      return true;
	      
	   return false;   
	}
	
	
	
	public function ClearCache()
	{
		$files = glob("../cache/*.*", GLOB_NOSORT);
		foreach ($files as $f)
			unlink($f);

		folderDelete("../cache/gallery");
		folderDelete("../cache/articles");
		folderDelete("../cache/videohref");
		
		$files = glob("./../admin/cache/*.*", GLOB_NOSORT);
		foreach ($files as $f)
			unlink($f);		
			
	}
	
	public function viewReferenceTable()
	{
		$sql = "SELECT r.url, m.title, l.`name` as language, r.id 
		FROM `references` r
		INNER JOIN a_modules m ON m.id = r.module_id
		INNER JOIN a_language l ON l.id = r.lang";
		
		$list = $this->db->db_dataset_array($sql);
		$content = "<h1>Таблица пользовательских ссылок</h1>
		 Ссылки заданные пользователем. Здесь НЕ указаны ссылки, формирующиеся автоматически по правилам движка.<br/><br/> 
		<table class='ref_table'><tr><th>Url</th><th>Модуль</th><th>Язык</th><th>Удалить</th></tr>";
		foreach ($list as $r)
			$content .= "<tr><td>".$r['url']."</td><td>".$r['title']."</td><td>".$r['language']."</td>
			<td><a href='javascript:void(0);' onclick=\"if (confirm('Удалить ссылку ".$r['url']."?') ) $.get('ajax.php?module=AdminMenu&method=delRef&id=".$r['id']."', {}, refresh);\">
				<img src='../admin/templates/images/delete.gif' /></a>	</td>
			</tr>";
		$content.= "</table>";
		
		$this->Content = $content;
	}
	
	public function delRef(&$data)
	{
		$sql = "DELETE FROM `references` WHERE id = ".(int)$data['id'];
		$this->db->db_query($sql);
	}
	
	// оптимизация таблиц
	public function OptimizeTable()
	{
		$sql = "OPTIMIZE TABLE articles_pages, e_category_pages, e_school_pages, e_country_pages, `references`, `e_school_lang`, `e_category_lang`, `e_country_lang`";
		$this->db->db_query($sql);
	}
}
?>
