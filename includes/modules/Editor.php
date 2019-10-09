<?php
class Editor extends Module{
	
	private $fileName = "";
	private $fileContent = "";
	private $menuList = "";
	public function loadMenu()
	{
	 $this->menuList = array(
		array('method'=>'folder', 'path'=>'../conf/'.$_SESSION['lang_folder'].'/', 'title' => 'Конфиги'),
		array('method'=>'edit', 'path'=>'../conf/mapping.cfg', 'title' => 'Cоответствия URL'),
		array('method'=>'edit', 'path'=>'../conf/404url.cfg', 'title' => 'Error 404 для URL'),
		array('method'=>'folder', 'path'=>'../templates/'.MAIN_TEMPLATE.'/box/', 'title' => 'Шаблоны блоков'),
		array('method'=>'folder', 'path'=>'../templates/'.MAIN_TEMPLATE.'/modules/', 'title' => 'Шаблоны модулей'),
		array('method'=>'folder', 'path'=>'../templates/'.MAIN_TEMPLATE.'/css/', 'title' => 'Таблицы стилей'),
		array('method'=>'folder', 'path'=>'../templates/'.MAIN_TEMPLATE.'/', 'title' => 'Шаблоны документов')
	);

		$result = "<ul>";
		foreach ($this->menuList as $item)
			$result .='<li><a href="admin/index.php?module=Editor&method='.$item['method'].'&path='.$item['path'].'">'.$item['title'].'</a></li>';
		$result .= "</ul>";
		
		return $result;
	}
	
	public function edit(&$data)
	{
		$tpl = new AdminTemplate;
		$tpl->assign('file_content', file_get_contents($data['path']));
		$tpl->assign('save_url', HOST.$SERVER['REQUEST_URI']);
		$tpl->assign('save_enabled', true);
		$tpl->assign('cancel_url', HOST.'admin/index.php');
		$tpl->assign('action_title', 'Редактировать файл');
		$tpl->assign('file_name', $data['path']);
		
		$this->Content = $this->fetchTemplate(CURRENT_TEMPLATE."modules/edit_file_form.html", $tpl);
	}
	
	public function save()
	{
		$f = fopen($_POST['file_name'], "w");
		fwrite($f, stripslashes($_POST['file_content']));
		fclose($f);
	}
	
	public function folder(&$data)
	{
		$tpl = new AdminTemplate;
			
		$list = get_file_list($data['path']);
		$files = array();
		for ($i=0; $i<count($list); $i++)
			if (is_file($data['path'].$list[$i]))
				$files[] = '<a href="admin/index.php?module=Editor&method='.$data['method'].'&path='.$data['path'].'&file='.$list[$i].'">'.$list[$i].'</a>';
				
		$tpl->assign_by_ref('list', $files);
		
		if (is_file($data['path'].$data['file'])){
			$tpl->assign('file_content', file_get_contents($data['path'].$data['file']));
			$tpl->assign('save_url', HOST.$SERVER['REQUEST_URI']);
			$tpl->assign('save_enabled', true);
			$tpl->assign('cancel_url', HOST.'admin/index.php?module=Editor&method='.$data['method'].'&path='.$data['path']);
			$tpl->assign('action_title', 'Редактировать файл');
			$tpl->assign('file_name', $data['path'].$data['file']);
		}
		else{
			$tpl->assign('cancel_url', HOST.'admin/index.php');
			$tpl->assign('save_enabled', false);
		}
		
		$this->Content = $this->fetchTemplate(CURRENT_TEMPLATE."modules/edit_file_form.html", $tpl);
		
	}
}
?>
