<?php
class Footer_hrefs extends Module
{
	function __construct(&$db, $params)
	{
		parent::__construct($db, $params);
		$this->table = "footer_hrefs";
	}

	public function LoadContent() {}
	
	public function LoadBoxContent()	{	}
	
	public function LoadBlock()
	{
		$list = $this->get_list();
		
		$tpl = new Template;
		$tpl->assign_by_ref('list', $list);
		
		return $this->fetchTemplate(CURRENT_TEMPLATE."modules/footer_hrefs", $this->tpl);	
	}
	
	function get_list()
	{
		$list = $this->GetItems();
		
		$result = array();
		foreach ($list as $item)
		{
			$anchor= $this->get_anchor($item);	
			$result[] = array('href' => $item['href'], 'anchor' => $anchor);
		}
		return $result;
	}
	
	function get_anchor(&$item)
	{
		$next_id = $item['anchor_id'];
		
		$p = abs(daysBetween($item['anchor_dt'], date("Y-m-d")));
		
		if ($p >= $item['period']){
			$next_id = $item['anchor_id']+1;
			if ($next_id >= count($item['anchors']) )
				$next_id = 0;
				
			$this->update_current_anchor($item['id'], $next_id);	
		}
		
		return $item['anchors'][$next_id];
	}
	
	
	function update_current_anchor($id, $anchor_id)
	{
		$sql = "UPDATE ".$this->table." SET
			anchor_id = ".$anchor_id.",
			anchor_dt = '".date("Y-m-d")."'
			WHERE id = ".$id;

		$this->db->db_query($sql);		
	}
	
	/*===========================  Admin    ==========================*/
	
	public function ItemsList(&$params)
	{
		if (isset($_POST['new_href']))
			$this->insert();
		
		if (isset($_POST['save']))
			$this->update();
		
		
		$this->module_url = HOST_ADMIN.'?module='.$params['module'];
		$this->method_url = '&method='.$params['method'];
		$list = $this->GetItems();

		$this->tpl->assign_by_ref('list', $list);
		$this->tpl->assign('module_url', $this->module_url);
		$this->tpl->assign('method_url', $this->method_url);
		
		$this->Content = $this->fetchTemplate(CURRENT_TEMPLATE."modules/footer_hrefs.html", $this->tpl);
		
		$this->Navigator = 'Ссылки с переменными якорями';
	}
	
	private function GetItems()
	{
		$sql = "SELECT * FROM ".$this->table." WHERE lang = ".$_SESSION['lang_id'];
 
		$list = $this->db->db_dataset_array($sql);

		if (count($list) ==0 ) $list = array();

		for ($i=0; $i<count($list); $i++)
			$list[$i]['anchors'] = json_decode(stripslashes($list[$i]['anchors']));
			
		return $list;	
	}
	
	
	function insert()
	{
		$sql ="INSERT INTO ".$this->table." (lang, href, period, anchor_dt, anchor_id, anchors) 
		VALUES (".$_SESSION['lang_id'].", 'url', 5, '".date('Y-m-d')."', 0, '[]')";
		
		$this->db->db_query($sql);
	}
	
	function update()
	{
		$anchors = '[';
		foreach ($_POST['anchor'] as $anchor)
			if ($anchor != '')
				$anchors .= '"'.$anchor.'",';
		$anchors = substr($anchors, 0, strlen($anchors)-1).']';		
		
		if(!isset($_POST['anchor_id'])) $_POST['anchor_id'] = 0;
		
		$sql = "UPDATE ".$this->table." SET
			href = '".$_POST['href']."',
			period = ".$_POST['period'].",
			anchor_id = ".$_POST['anchor_id'].",
			anchors = '".addslashes($anchors)."'
			WHERE id = ".(int)$_POST[id];

		$this->db->db_query($sql);
	} 
	
	function del(&$params)
	{
		$sql = "DELETE FROM ".$this->table." WHERE id = ".(int)$params['id'];
		$this->db->db_query($sql);
	}
}
?>
