<?php
class Subscribe extends Module{
	

	function __construct(&$db, &$params)
	{
		parent::__construct($db, $params);
		
		define('SUBSCRIBE_TITLE', '<h2>Список email подписчиков</h2>');
	}

	
	public function LoadBoxContent(){}
	public function LoadContent(){}
	
	public function subscribe(&$data){
	    if (isset($data['email']) && $data['email']!= ''){
		
	      $sql = "INSERT IGNORE INTO subscribe (email, insert_date) VALUES ('".addslashes($data['email'])."', '".date("Y-m-d H:i:s")."')";
	      $this->db->db_query($sql);
	    }
	}
	
	public function get_all()
	{
			$this->Content .= SUBSCRIBE_TITLE;
			
			$sql = "SELECT email FROM subscribe";
			$list = $this->db->db_dataset_array($sql);
	
			if (count($list) > 0)
				foreach ($list as $item)
					$this->Content .= $item['email']."<br/>";
			else	
				$this->Content .= "Ничего нет";
	}
	
	public function get_new()
	{
		$sql = "SELECT email FROM subscribe WHERE state = 0";
		$list = $this->db->db_dataset_array($sql);
		
		$this->Content .= SUBSCRIBE_TITLE;
		if (count($list) > 0)
			foreach ($list as $item)
				$this->Content .= $item['email']."<br/>";
		else
			$this->Content .= "Новых нет.  Возможно Вы уже просматривали новых подписчиков. Смотрите 'Все'";		
	
		$sql = "UPDATE subscribe SET state = 1 WHERE state = 0";
		$this->db->db_query($sql);
	}
}
?>
