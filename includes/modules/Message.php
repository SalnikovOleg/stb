<?php
class Message extends Module{
	private $id = 0;
	private $email = '';
	private $phone = '';
	private $name = '';
	private $message = '';
	private $sended = 0;
	private $page_url = '';
		
	function __construct(&$db, &$params)
	{
		parent::__construct($db, $params);
	
		$this->table = "messages";
	}
	
	public function saveForm($p) {
		if ($_POST['fio'] == 'Ф.И.О.') 
			$_POST['fio'] = '';
		if ($_POST['email'] == 'E-MAIL')
			$_POST['email'] = '';
		if ($_POST['phone'] == 'ТЕЛЕФОН')
			$_POST['phone'] = '';	
			
		$this->save($_POST);
		echo $this->id; 
		die();
	}
	
	public function save($data) {
		$this->prepare($data);
		
		if ($this->id){
			$this->update();
		} else {
			$this->insert();
		}
	}
	
	private function prepare($data){
		if (isset($data['id']))
			$this->id = (int)$data['id'];
		if (isset($data['fio']))
			$this->name = substr($data['fio'],0,100);
		if (isset($data['phone']))
			$this->phone = substr($data['phone'],0,100);
		if (isset($data['email']))
			$this->email = substr($data['email'],0,100); 
		if (isset($data['message']))
			$this->message = $data['message'];
		if (isset($data['sended']))
			$this->sended = $data['sended'];
		else 	
			$this->sended = 0;
		if (isset($data['url']))
			$this->page_url = $data['url'];
	}
	
	private function insert(){
		$sql = "INSERT INTO ".$this->table." (name, phone, email, message, page_url, sended) VALUES (
			'".$this->name."', '".$this->phone."', '".$this->email."', '".addslashes($this->message)."', '".$this->page_url."', ".$this->sended.")";

		$this->db->db_query($sql);
		$this->id = $this->db->get_insert_id();
	}
	
	private function update(){
		$sql = "UPDATE ".$this->table." SET 
		name = '".$this->name."',
		phone = '".$this->phone."',
		email= '".$this->email."',
		message= '".addslashes($this->message)."',
		page_url= '".$this->page_url."',
		sended= ".$this->sended."
		WHERE id=".$this->id;
		$this->db->db_query($sql);
	} 
	
	//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	public function ItemsList($params) {
		$this->module_url = HOST_ADMIN.'?module='.$params['module'];
		$this->method_url = '&method='.$params['method'];
		
		$dt_to = date("Y-m-d");
		$dt_from = date("Y-m-d", dateAdd('d', -10, $dt_to));

		$p = array('sended'=>1, 'dt_from'=> $dt_from, 'dt_to' => $dt_to);
		
		if (isset($_POST['dt_from']))	{
			$p['sended'] = bool_to_int($_POST, 'sended');
			$p['dt_from'] = $_POST['dt_from'];
			$p['dt_to']	= $_POST['dt_to'];
		}
		
		$list = $this->GetItems($p);

		$this->tpl->assign_by_ref('list', $list);
		$this->tpl->assign('module_url', $this->module_url);
		$this->tpl->assign('method_url', $this->method_url);
		$this->tpl->assign('language', $_SESSION['lang_folder']);
		$this->tpl->assign('params', $p);
		
		$this->Content = $this->fetchTemplate(CURRENT_TEMPLATE."modules/message_list.html", $this->tpl);
	}
	
	private function getItems($params) {
		$sql = "SELECT * FROM ".$this->table." WHERE dt BETWEEN '".$params['dt_from']." 00:00:00' AND '".$params['dt_to']." 23:59:59' AND sended = ".$params['sended']." ORDER BY dt DESC";
		$list = $this->db->db_dataset_array($sql);
		if (count($list)) {
			foreach ($list as &$row){
				$row['message'] = stripslashes($row['message']);
			}
		} else {
			$list = array();
		}
		return $list;
	}
}
?>
