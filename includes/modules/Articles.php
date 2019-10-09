<?php
class Articles extends PagesTree
{
	public function __construct(&$db, &$params) 
	{
		parent::__construct($db, $params);

		$this->table = "articles";

		$this->folder = "articles";
		//$this->module_url = "articles/";
		
		if ($this->realModuleUrl == '') $this->realModuleUrl = 'articles';
		if ($this->mappedModuleUrl == '') $this->mappedModuleUrl = $this->realModuleUrl;

		$this->main_selected_articles_file = './cache/selected_'.$_SESSION['lang_url'].'_articles.php';
				
		create_dir('./cache/articles/');
	}

	public function GetSubNodes($url)
	{
		if ($url == $this->table) 
			return $this->db->get_top_nodes($this->table);
			
		$sql = "SELECT 0 AS id, -1 AS page_id, '' AS `url`, n.`name`, null AS description, null AS insert_date 	FROM ".$this->table." n WHERE n.url = '".$url." '
			UNION
			SELECT n.id, IFNULL(n.page_id, 0) AS page_id, REPLACE(n.url,' ','') AS url, n.`name`, p.description, n.insert_date 
			FROM ".$this->table." n 
			LEFT JOIN ".$this->table."_pages AS p ON p.id = n.page_id
			WHERE  n.parent in (SELECT id FROM ".$this->table." WHERE url='".$url."') AND n.`disabled`=0 AND n.`deleted` = 0  AND n.`lang`=".$_SESSION['lang_id']." 
			ORDER BY page_id, insert_date";

		return $this->db->db_dataset_array($sql);
	}

	// блок отображения выделеных статей
	public function LoadSelected()
	{
		
		$sql = "SELECT t1.* FROM (
		SELECT n.`name`,  np.description, n.parent_url, n.url, n.absolute_url, n.image, n.image_alt, n.insert_date 
		FROM ".$this->table." n 
		INNER JOIN ".$this->table."_pages np ON np.id = n.page_id
		WHERE n.page_id IS NOT NULL AND n.lang = ".$_SESSION['lang_id']." AND deleted = 0 AND disabled = 0
		ORDER BY insert_date DESC	limit 0, 3 ) t1	
		UNION
		SELECT n.`name`,  np.description, n.parent_url, n.url, n.absolute_url, n.image, n.image_alt, n.insert_date 
		FROM ".$this->table." n 
		INNER JOIN ".$this->table."_pages np ON np.id = n.page_id
		WHERE n.page_id IS NOT NULL AND n.selected = 1 AND n.lang = ".$_SESSION['lang_id']." AND deleted = 0 AND disabled = 0
		ORDER BY insert_date DESC ";

		$this->loadArticles($this->main_selected_articles_file, $sql);

		$this->tpl->assign('more_url', $this->mappedModuleUrl); 
		
		return $this;			
	}
	
	// формирование блоков статей
	protected function loadArticles($file, $sql)
	{
		if (!file_exists($file)) {
			$list = $this->db->db_dataset_array($sql);
		
			/*$content = "<?php\n";*/
			for ($i=0; $i<count($list); $i++){
				$list[$i]['description'] = quote_replace(stripslashes($list[$i]['description']));
				$list[$i]['insert_date'] = YMDToDMY(substr($list[$i]['insert_date'],0,10));
				$list[$i]['url'] = $this->createUrl($list[$i]['url'], $list[$i]['absolute_url'], $list[$i]['parent_url'], false);
				//$content .= '$list[]=array("name"=>"'.$list[$i]['name'].'", "description"=>"'.$list[$i]['description'].'", "url"=>"'.$list[$i]['url'].'", "image"=>"'.$list[$i]['image'].'", "image_alt"=>"'.$list[$i]['image_alt'].'", "insert_date"=>"'.$list[$i]['insert_date'].'");'."\n";
			}	
			/*$content .= "?>";*/
			$content = json_encode($list);
			file_put_contents($file, $content);
		}
		else {
			$content = file_get_contents( $file );
			$list = json_decode($content, true);
		}

		if ($this->Params['params'] != ''){
			$params = json_decode($this->Params['params'], true);		
                        $maxcount = $params['maxcount'];

                }
		else
			$maxcount = count($list);
			
		$this->tpl->assign('maxcount', $maxcount); 
		$this->tpl->assign('name', $this->Caption);
		$this->tpl->assign('language', $_SESSION['lang_folder']);
		$this->tpl->assign('folder_img', $this->path_folder.$this->folder);
		$this->tpl->assign_by_ref('list', $list);
	}
	
	public function ItemsList(&$params)
	{
		$this->Params['template'] = "list.html";
		parent::ItemsList($params);
	}
	
}
?>
