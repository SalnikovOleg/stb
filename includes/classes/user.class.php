<?php
class User
{
	public $Logged = false;
	public $UserId = 0;
	public $UserName = "Unknown";
	public $RoleId = 0;
	public $RoleName = "";
	public $UserEmail = "";
	private $Password = "";
	private $Login = "";
	private $db = null;
	private $GUEST_LIVE_TIME = 2;
	
	function __construct(&$db, $logged)
	{
		$this->db = $db;
		$this->Logged = $logged;
	}
	
	//  выход. уничтожение данных пользователя в сессии
	public static function LogOut()
	{
		unset($_SESSION['DocumentId']);
		unset($_SESSION['UserId']);
		unset($_SESSION['UserName']);
		unset($_SESSION['RoleName']);
		unset($_SESSION['RoleId']);
		unset($_SESSION['UserEmail']);
		unset($_SESSION['Login']);
		unset($_SESSION['Password']);
	}
	
	// проверка логина и пароля и запись данных пользователя в сессию
	public function LogIn($docId)
	{
		if ( isset($_POST['login']) && isset($_POST['password']) )
			return $this->LoadUser($_POST['login'], $_POST['password'], $docId);
		else 
			return false;	
	}

	// проверка логина в сессии и загрузка данных пользователя из сессии
	public function CheckLogin()
	{
		if ( isset($_SESSION['Login']) && isset($_SESSION['Password']) )
			{
				$this->LoadFromSession();
				return true;
			}
		else
			return false;
	}
	
	// получить данные пользователя по логину и проверить пароль 
	private function LoadUser($login, $password, $docId)
	{
		$data = $this->db->get_user($login, $docId);

		if (($data == null) || ($data['password'] != md5($password) ))
		{
			$_SESSION['ErrorMessage'] = "Неверный логин или пароль";
			return false;
		}
		else
		{
			$this->SaveToSession($data);
			return true;
		}
	}
	
	private function SaveToSession($data)
	{
		$_SESSION['DocumentId'] = $data['doc_id'];
		$_SESSION['UserId'] = $data['id'];
		$_SESSION['UserName'] = $data['user_name'];
		$_SESSION['RoleName'] = $data['role_name'];
		$_SESSION['RoleId'] = $data['role_id'];
		$_SESSION['UserEmail'] = $data['email'];
		$_SESSION['Password'] = $data['password'];
		$_SESSION['Login'] = $data['login'];
		$_SESSION['ErrorMessage'] = "";
		//$_SESSION['SelectedBranchId'] = $data['branch_id'];
	}
	
	private function LoadFromSession()
	{
		$this->DocumentId = $_SESSION['DocumentId'];
		$this->UserId = $_SESSION['UserId'];
		$this->UserName = $_SESSION['UserName'];
		$this->RoleName = $_SESSION['RoleName'];
		$this->RoleId = $_SESSION['RoleId'];
		$this->UserEmail = $_SESSION['UserEmail'];
		$this->Login = $_SESSION['Login'];
		$this->Password = $_SESSION['Password'];
	}
	
	public function CheckNewGuest()
	{
		if (isset($_SESSION['UserId'])) 
			return;
	
		$this->DeleteOldCustomers();
				
		$id = $this->db->get_next_id('a_guests', 'id');

		$sql = 'insert into '.T_GUESTS.' (id, insert_date, session_id, ip_address) values('.$id.', "'.date("Y-m-d H:i:s").'", "'.session_id().'", "'.GetRealIp().'")';

		$this->db->db_query($sql);  
		
		$_SESSION['UserId'] = $id;
		
		// признак нового гостя
		$_SESSION['NewUser'] = 1;
		
		$this->UserId = $id;

	}
	
	private function DeleteOldCustomers()
	{
		//$limit = date("Y-m-d H:i:s", time() - $this->GUEST_LIVE_TIME * 3600 );
		$sql='DELETE FROM  '.T_CART.' WHERE customer_status=-1 AND customer_id IN (SELECT id FROM '.T_GUESTS.' WHERE insert_date<= DATE_ADD(NOW(), INTERVAL -1 DAY))';
		$this->db->db_query($sql);
		
		$sessions = $this->db->db_get_list('SELECT id, session_id FROM '.T_GUESTS.' WHERE insert_date<DATE_ADD(NOW(), INTERVAL -1 DAY)');
		if (count($sessions)>0)
		foreach($sessions as $sessionId)
			if (file_exists(SESSION_PATH.'sess_'.$sessionId) != false) 
				unlink(SESSION_PATH.'sess_'.$sessionId);
			
		$sql= 'DELETE FROM '.T_GUESTS.' WHERE insert_date<DATE_ADD(NOW(), INTERVAL -1 DAY)';

		$this->db->db_query($sql);
	}

}
?>