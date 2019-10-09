<?php
class MainMenu extends Module
{
	private $cache_file = "";
	
	function __construct(&$db, $params)
	{
		parent::__construct($db, $params);
		$this->table = "mainmenu";
		
		$this->cache_file = $this->table."_".$_SESSION['lang_url'].".html";
		$this->sitemap_file = "map_".$this->table."_".$_SESSION['lang_url'].".html";	

	}

	public function LoadContent() {}
	
	public function LoadBoxContent()
	{
		$this->CreateTree();
		$this->tpl->assign_by_ref('list', $this->tree);
		
		if (trim($this->query[$this->last]) == "" || $this->last < MODULEINDEX ) $a_id = "main";
		else $a_id = str_replace('.html', '', $this->query[$this->last]);
		
		//$this->tpl->assign('active_id', '<script type="text/javascript">var active_id = "'.$a_id.'"</script>');		
	}

		protected function CreateTree()
		{/*
			if (file_exists('./cache/'.DEFAULT_DOC."_".$this->cache_file) != false)
				$this->tree = file_get_contents('./cache/'.DEFAULT_DOC."_".$this->cache_file);
			else
			{*/
				$this->tree .= "<ul class=\"nav navbar-nav\">\n";
				$this->GetNode(0, 0, "");
				$this->tree .= "</ul>\n";
				write_to_file('./cache/'.DEFAULT_DOC."_".$this->cache_file, $this->tree);
			//}
		}
		
		protected function GetNode($cId, $level, $preUrl)
		{
			if (DEFAULT_DOC == 1)
				$docs = '1,3';
			else
				$docs = '2,3';
				
			$sql = "SELECT m.`id`, m.`parent`, m.`node`, r.`url`, m.`name`, mt.`method`, md.`name` AS `module`, m.absolute_url
				FROM ".$this->table." m	
				INNER JOIN `references` r ON r.id = m.reference_id AND r.lang = ".$_SESSION['lang_id']."
				LEFT JOIN a_modules_methods mt ON mt.id = m.method_id
				LEFT JOIN a_modules md ON md.id = mt.module_id
				WHERE m.parent = ".$cId." 
					AND m.lang = ".$_SESSION['lang_id']." 
					AND m.deleted = 0 
					AND m.disabled=0 
					AND m.doc_bin IN (".$docs.")
				ORDER BY ordno";

			$ds = $this->db->db_dataset_array($sql);
	
			for ($i=0; $i<count($ds); $i++)
			{
				if (trim($ds[$i]['url']) == "")	$a_id = "main";
				else $a_id = str_replace('.html', '', $ds[$i]['url']);			

				if ($ds[$i]['url'] == '-')
					$url = "javascript:{};";
				else
					$url = $this->createUrl($ds[$i]['url'], $ds[$i]['absolute_url'], $preUrl, false);		

				if ($i == count($ds)-1) $class='class="last"'; else $class='';
				
				if ($ds[$i]['node'] == 1)
				{
					//$this->tree .= '<li><a id="'.$a_id.'" '.$class.' href="'.HOST.$lang_url.$preUrl.trim($ds[$i]['url']).$urlEnd.'">'.$ds[$i]['name']."</a>\n";				
					//$this->tree .= '<li class="dropdown"><a id="'.$a_id.'" '.$class.' href="'.$url.'" class="dropdown-toggle" data-toggle="dropdown">'.$ds[$i]['name']."<span class=\"caret\"></span></a>\n";
					$this->tree .= '<li class="dropdown"><a id="'.$a_id.'" '.$class.' href="'.$url.'">'.$ds[$i]['name']."</a>\n";
					$this->tree .="\n<ul class=\"dropdown-menu\">\n";

					//if (trim($ds[$i]['url']) != "")	$preUrl = $preUrl.$ds[$i]['url'].'/';
					if (trim($ds[$i]['url']) != "")	$preUrl = $preUrl.$url;
					
					$this->GetNode($ds[$i]['id'], $level + 1, $preUrl);
					$this->tree .="</ul></li>\n";
				}
				else
				{
					//$this->tree .= '<li><a id="'.$a_id.'"  '.$class.' href="'.HOST.$lang_url.$preUrl.trim($ds[$i]['url']).$urlEnd.'">'.$ds[$i]['name']."</a>\n";
					$this->tree .= '<li><a id="'.$a_id.'"  '.$class.' href="'.$url.'">'.$ds[$i]['name']."</a>\n";
					
					if(isset($ds[$i]['module']) && trim($ds[$i]['module']) != "" )
					{
						$moduleName = $ds[$i]['module'];
						$methodName = $ds[$i]['method'];
						includeModule($moduleName);
						$params = $this->db->get_moduleByName($moduleName, DEFAULT_DOC);
						$M = new $moduleName($this->db, $params);
						$this->tree .= $M->$methodName();
					}
					
					$this->tree .= "</li>\n";
				}	
			}
		}


	public function LoadBottomMenu()
	{
		if (file_exists('./cache/bottom_'.$this->cache_file) != false)
			$this->tree = file_get_contents('./cache/bottom_'.$this->cache_file);
		else
			{
				$this->tree .= "<ul class=\"menu\">\n";
				$this->GetTopNode();
				$this->tree .= "</ul>\n";
				write_to_file('./cache/bottom_'.$this->cache_file, $this->tree);
			}
		$this->tpl->assign_by_ref('list', $this->tree);
	}
	
	private function GetTopNode()
	{
		$preUrl ="";
		$sql = "SELECT m.`id`, r.`url`, m.`name`, m.`absolute_url`  FROM ".$this->table." m INNER JOIN `references` r ON r.id = m.reference_id AND r.lang = ".$_SESSION['lang_id']."
			WHERE m.parent = 0 AND m.lang = ".$_SESSION['lang_id']. " AND disabled = 0";
			
		$ds = $this->db->db_dataset_array($sql);
		for ($i=0; $i<count($ds); $i++)
		{
			if ($ds[$i]['url'] != '-') {
				$url = $this->createUrl($ds[$i]['url'], $ds[$i]['absolute_url'], $preUrl, false);
				if ($i == count($ds)-1) $class='class="last"'; else $class='';
		
				$this->tree .= '<li><a '.$class.' href="'.$url.'" >'.$ds[$i]['name']."</a></li>\n";
			}					
		}
	}
	
		
	public function CreateHtmlSiteMap($id, $root)
	{
		$this->sitemap = "<b>".$this->Caption."</b><br><ul>\n";
		$this->GetHtmlNode($id, 0, "");
		$this->sitemap .= "</ul>\n";
		
		write_to_file($root."cache/".$this->sitemap_file, $this->sitemap);		
	}

	private function GetHtmlNode($cId, $level, $preUrl)
	{
		$ds = $this->SelectItems($cId);
		
		for ($i=0; $i<count($ds); $i++)
		{
			$url = HOST.langUrl($_SESSION['lang_url']).$preUrl.addEndSlash(trim($ds[$i]['url']));
		
			if ($ds[$i]['node'] > 0)
			{
				$this->sitemap .= '<li><a class="node" href="'.$url.'" >'.$ds[$i]['name']."</a>";				
				$this->sitemap .="\n<ul>\n";
				$this->GetHtmlNode($ds[$i]['id'], $level + 1, $preUrl.addEndSlash(trim($ds[$i]['url'])));
				$this->sitemap .="</ul>\n";
			}
			else
 				$this->sitemap .= '<li><a class="node" href="'.$url.'" >'.$ds[$i]['name']."</a>";				
		}
	}
	

	public function CreateXMLSiteMap($id, $root)
	{
		$this->sitemap = '<?xml version="1.0" encoding="UTF-8"?>'."\n".'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";
		$this->GetXMLNode($id, 0, "");
		$this->sitemap .= '</urlset>';
			
		write_to_file($root.$this->table.".xml", $this->sitemap);	
	}
	
	public function GetXMLNode($cId, $level, $preUrl)
	{
		$ds = $this->SelectItems($cId);
		
		for ($i=0; $i<count($ds); $i++)
		{
			$url = HOST.langUrl($_SESSION['lang_url']).$preUrl.addEndSlash(trim($ds[$i]['url']));

			$this->sitemap .= "<url>\n\t<loc>".$url."</loc>\n\t<lastmod>".$ds[$i]['insert_date']."</lastmod>\n</url>\n";				
				
			if ($ds[$i]['node'] > 0)
				$this->GetXMLNode($ds[$i]['id'], $level + 1, $preUrl.addEndSlash(trim($ds[$i]['url'])));
		}
	}
		

	public function SelectItems($id)
	{
		$sql = "SELECT c.id, c.parent, c.`node` , r.url, c.`name`, null AS insert_date FROM ".$this->table." c 
		INNER JOIN `references` r ON r.id = c.reference_id
		WHERE parent = ".$id." AND disabled=0 AND deleted = 0 AND c.lang = ".$_SESSION['lang_id'];

		return $this->db->db_dataset_array($sql);
	}		
//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++		
//---------------------------   admin  ------------------------------------------
//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	public function ItemsList(&$params)
	{
		if (!isset($params['pId'])) $id = 0; 
		else $id = (int)$params['pId'];
		
		$this->module_url = HOST_ADMIN.'?module='.$params['module'];
		$this->method_url = '&method='.$params['method'];
		

		$list = $this->GetItems($id);
		for ($i=0; $i<count($list); $i++ )
			$list[$i]['url'] = $this->module_url.$this->method_url."&pId=".$list[$i]['id'];
			
		$this->tpl->assign_by_ref('list', $list);
		$this->tpl->assign('module_url', $this->module_url);
		$this->tpl->assign('method_url', $this->method_url);
		$this->tpl->assign('edit_url', '&method=EditItem');
		$this->tpl->assign('add_url', '&method=NewItem');
		$this->tpl->assign('parent_url', '&pId='.$id);
		$this->tpl->assign('ajax_url', HOST."admin/ajax.php?module=".$params['module']);
		
		if (file_exists("./templates/".CURRENT_TEMPLATE."modules/".$this->Params['template']) == true) 
			$this->Content = $this->tpl->fetch(CURRENT_TEMPLATE."modules/".$this->Params['template']);
		else 	
			$this->Content = "ÐÐµ Ð½Ð°Ð¹Ð´ÐµÐ½ ÑÐ°Ð±Ð»Ð¾Ð½ ".CURRENT_TEMPLATE."modules/".$this->Params['template'];
		
		$this->AdminNavigator($id);
	}


	private function GetItems($id)
	{
		$sql = "SELECT m.`id`, m.`name`, r.`url`, r.`url` as `href`, m.`ordno`, m.`disabled`, m.node FROM ".$this->table." m
			INNER JOIN `references` AS r ON r.`id` = m.`reference_id`
		WHERE m.`lang` = ".$_SESSION['lang_id']." AND `deleted` = 0 AND parent =".$id." ORDER BY m.`disabled`, m.`ordno` ";

		return $this->db->db_dataset_array($sql);
	}

	

	protected function AdminNavigator($id)
	{
		parent::AdminNavigator("");
		
		$href = array();
		
		$this->GetParent($id, $href);
		
		$href = array_reverse($href);
		
		foreach ($href as $item)
			$this->Navigator .= ' :: '.$item;

	}
	

	private function GetParent($parent, &$href)
	{
		$item = $this->db->db_get_array("SELECT `id`, `parent`, `name` FROM ".$this->table." n WHERE n.id = ".$parent);
		$href[] = '<a href="'.$this->module_url.$this->method_url.'&pId='.$item['id'].'">'.$item['name'].'</a>';
		if ($item['parent'] != 0 )
			$this->GetParent($item['parent'], $href);
	}
	

	public function EditItem(&$params)
	{
		if (!isset($params['itemId'])) $id = 0; 
		else $id = (int)$params['itemId'];
		
		if (!isset($params['action'])) 
		{
			$params['action'] = 'update';
			$params['action_title'] = 'Редактировать ';
		}

		$this->module_url = HOST_ADMIN.'?module='.$params['module'];
		$this->method_url = '&method=ItemsList';
		
		$item = $this->GetItem($id);
	
		if (!isset($params['pId']) ) $params['pId'] = 0;
		$item['parent'] = $params['pId'];

		$item['doc_main'] = 0;
		$item['doc_inner'] = 0;
		if ($item['doc_bin']==1){ $item['doc_main'] = 1; }
		if ($item['doc_bin']==2){ $item['doc_inner'] = 1; }
		if ($item['doc_bin']==3){ $item['doc_main'] = 1; $item['doc_inner'] = 1;}
			
		$modules_data = $this->db->db_dataset_array("SELECT m.`id`, IFNULL(mtl.`name`, m.title) as `name`, m.`maybe_page` FROM a_modules AS m 
		LEFT JOIN a_modules_to_lang AS mtl ON mtl.`id`= m.`id` AND mtl.`lang` = ".$_SESSION['lang_id']."
		WHERE m.`formenu`=1 AND m.disabled = 0");
		
		foreach ($modules_data AS $md)
			$modules[$md['id']] = $md['name']; 
		
		$pages = $this->db->get_all_pages();

		$this->tpl->assign_by_ref('item', $item);

		$this->tpl->assign('modules', select_list($modules, 'module_id', 'onChange="moduleChange(this.value);"', $item['module_id']));
		$this->tpl->assign('module_array', java_array($modules_data, 'maybe_page', 'id', 'maybe_page'));
		$this->tpl->assign('pages', select_list($pages, 'page_id', '', $item['page_id'], -1, '--- Ð²ÑÐ±ÐµÑÐ¸ÑÐµ ÑÑÑÐ°Ð½Ð¸ÑÑ ---'));
		$this->tpl->assign('action_title', $params['action_title']);
		$this->tpl->assign('action', $params['action']);
		$this->tpl->assign('module_url', $this->module_url);
		$this->tpl->assign('method_url', $this->method_url);
		$this->tpl->assign('parent_url', '&pId='.$params['pId']);
		
		if (file_exists("./templates/".CURRENT_TEMPLATE."modules/".$this->table."_form.html") == true) 
			$this->Content = $this->tpl->fetch(CURRENT_TEMPLATE."modules/".$this->table."_form.html");
		else 	
			$this->Content = "Не найден шаблон ".CURRENT_TEMPLATE."modules/".$this->table."_form.html";		
			
		$this->AdminNavigator($id);	

	}
	

	private function GetItem($id)
	{
		if ($id != 0)
		{
			$sql="SELECT m.`id`, m.`name`, m.`disabled`, m.`ordno`, r.`module_id`, IFNULL(r.`page_id`, 0) AS `page_id`, 
			r.`url`, CASE WHEN r.`url` = '' THEN 1 ELSE 0 END AS `starting`, p.`title`, p.`text`, m.`node`, m.`parent`,
			m.method_id, md.`maybe_page`, m.`reference_id`, m.`absolute_url`, m.doc_bin
			FROM ".$this->table." m
			INNER JOIN `references` AS r ON r.`id` = m.`reference_id`
			LEFT JOIN `a_modules` md ON md.id = r.`module_id`
			LEFT JOIN pages AS p ON p.`id` = r.`page_id`
			WHERE m.`id` = ".$id;

			$item = $this->db->db_get_array($sql);
		}
		else
			$item = array('parent'=>0, 'name'=>'', 'disabled'=>0, 'ordno'=>0, 'module_id'=>-1, 'url'=>'', 'starting'=>0, 'node'=>0, 'maybe_page'=>0, 'doc_bin'=>3 );

		return $item;
	}
	
	// -----------------   save data   -----------------------
	
	public function insert(&$data)
	{
		$reference_id = $this->get_reference($data);

		if (trim($data['module_id']) != '')
			$method_id = $this->get_methodId($data['module_id'], 'CreateMenu');
		else 
			$method_id = 'null';
			
		$doc_bin = $this->doc_bin($data);
			
		$sql = "INSERT INTO ".$this->table." (`name`, `disabled`, `ordno`, `lang`, `reference_id`, `parent`, `node`, `method_id`, `absolute_url`, `doc_bin` ) 
		VALUES('".$data['name']."', ".bool_to_int($data, 'disabled').", ".$data['ordno'].", ".$_SESSION['lang_id'].", "
			.$reference_id.", ".$data['parent'].", ".bool_to_int($data,'node').", ".$method_id.", ".bool_to_int($data, 'absolute_url').", ".$doc_bin.")";

		$this->db->db_query($sql);
		$this->flush_cache();
	}
	
	public function update(&$data)
	{
		$reference_id = $this->get_reference($data);	
			
		if (trim($data['module_id']) != '')
			$method_id = $this->get_methodId($data['module_id'], 'CreateMenu');
		else 
			$method_id = 'null';
		
		$doc_bin = $this->doc_bin($data);
			
		$sql = "UPDATE ".$this->table." SET
			`name` = '".$data['name']."',
			`ordno` = ".$data['ordno'].",
			`disabled` = ".bool_to_int($data, 'disabled').",
			`reference_id` = ".$reference_id.",
			`node` = ".bool_to_int($data,'node').",
			`method_id` = ".$method_id.",
			`absolute_url` = ".bool_to_int($data, 'absolute_url').",
			`doc_bin` = ".$doc_bin."
			WHERE id = ".$data['id'];

		$this->db->db_query($sql);
		$this->flush_cache();
	}

	function doc_bin(&$data)
	{
		if (bool_to_int($data, 'doc_main') && bool_to_int($data, 'doc_inner'))
			return 3;
		elseif 	(bool_to_int($data, 'doc_main') )
			return 1;
		elseif 	(bool_to_int($data, 'doc_inner'))
			return 2;
		else	
			return 3;	
	}
	
	private function get_reference(&$data)
	{
		if ($data['starting']) {
	
			$reference_id = $this->selectReference("");
			if ($reference_id == null)
				$reference_id = $this->insertReference("", $data['module_id'], $data['page_id']);
			else
				$this->updateReference($reference_id, "", $data['module_id'], $data['page_id']);
			
			return 	$reference_id;
		}
		
		if (trim($data['url']) != "" && trim($data['url']) !== "-") 
			$data['url'] = createUrl($data['url'],0, "");
		else
			if (!isset($data['absolute_url']))
				$data['url'] = createUrl(utf2str($data['name'], "w"), 1, ".".DEFAULT_EXT);

		if (trim($data['module_id']) == "") $data['module_id'] = 0;

		if (isset($data['absolute_url'])){
			
			$reference_id = $this->selectReference($data['url'], $data['module_id']);

			if ((int)$reference_id == 0){
				if ((int)$data['page_id'] <= 0  ) $data['page_id'] = 'null';
				return $this->insertReference($data['url'], $data['module_id'], $data['page_id']);
			}	
			else
				return $reference_id;
		}
		

		if ($data['module_id'] > 0 && $data['maybe_page'] != 1){

			$reference_id = $this->selectReference(null, $data['module_id']);

			if (trim($reference_id) == "") 
				$url = $this->db->db_get_value("SELECT `url` FROM `a_modules` WHERE `id` = ".$data['module_id']);
			else			
				$url = $this->db->db_get_value("SELECT url FROM `references` WHERE id = ".$reference_id);
		}

		
		if ($data['maybe_page'] == 1)
		{	
			
			$reference_id = $this->selectReference(null, $data['module_id'], $data['page_id']);

			if ((int)$reference_id == 0) 
				$reference_id = $this->selectReference($data['url'], $data['module_id']);

			if ((int)$reference_id == 0) 
				$reference_id = $this->selectReference($url, $data['module_id']);
	
			if ( (int)$reference_id > 0 ) 
			{
			
				$url = $this->db->db_get_value("SELECT url FROM `reference` WHERE id = ".$reference_id);
				
				if ($data['url'] != '' && $data['url'] != $url) $url = $data['url']; 
				
				$sql = "UPDATE `references` SET page_id = ".$data['page_id'].", url = '".$url."', `module_id` = ".$data['module_id']." 	WHERE id = ".$reference_id." AND lang=".$_SESSION['lang_id'];
				$this->db->db_query($sql);
				
				return $reference_id;
			}
		}
	
		if ((int)$reference_id == 0){
			if ((int)$data['page_id'] <=0 ) $data['page_id'] = 'null';
			if ($url == '') $url = $data['url'];
			return $this->insertReference($url, $data['module_id'], $data['page_id']);
		}	
		else
			return $reference_id;
	}
	

	protected function insertReference($url, $module_id, $page_id)
	{
		$sql = "INSERT INTO `references` (`url`, `module_id`, `page_id`, `lang`) VALUES ('".$url."', ".$module_id.", ".$page_id.", ".$_SESSION['lang_id'].")"; 
		$this->db->db_query($sql);
		return $this->db->get_insert_id();		
	}
	

	private function updateReference($id, $url, $module_id = "", $page_id = "")
	{
		$module = "";
		if ($module_id != "" ) $module = ", module_id = ".$module_id;
		$page = "";
		if ($page_id != "") $page = ", page_id = ".$page_id;
		
 		$sql = "UPDATE `references` SET url ='".$url."' ".$page.$module." WHERE id = ".$id." AND lang=".$_SESSION['lang_id'];
		$this->db->db_query($sql);
	}
	

	private function selectReference($url, $module_id = null, $page_id = null)
	{
		$page = "";
		if ($page_id !=null)
			$page = " AND page_id = ".$page_id;
			
		$module = "";	
		if ($module_id !=null)
			$module = " AND module_id = ".$module_id;
			
		$u = "";
		if ($url != null)
			$u = " AND url = '".$url."'";
			
		$sql = "SELECT `id` FROM `references` WHERE lang=".$_SESSION['lang_id'].$module.$page.$u." limit 0,1";
		return $this->db->db_get_value($sql);
	}
	

	private function get_methodId($module_id, $methodName)
	{
		$sql = "SELECT id FROM a_modules_methods WHERE module_id = ".$module_id." AND method = '".$methodName."'";
		$id = $this->db->db_get_value($sql);
		if ($id == null || is_numeric($id) == false) $id = 0;
		
		return $id;
	}
}

?>
