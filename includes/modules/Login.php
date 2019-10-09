<?php
class Login extends Module
{
	function __construct(&$db, &$params)
	{
		$this->db = $db;
		$this->Params = $params;
		$this->query = explode("/", $_SERVER['REQUEST_URI']);
	}
	
	public function LoadContent()
	{
		if ($this->query[MODULEINDEX+1] == 'logout')
		{
			User::Logout();
			header("Location:".HOST.'login/');
			exit();
		}	

		$U = new User($this->db, false);
		if ( $U->Login(DEFAULT_DOC) )
			header("Location:".$_POST['request_uri']);
		else{
			//header("Location:".$_POST['login_uri']);
		}	
	}
	
	public function LoadBoxContent()
	{
		if (isset($_SESSION['ErrorMessage']))
		{
			$this->tpl->assign('ErrorMessage', $_SESSION['ErrorMessage']);
			unset($_SESSION['ErrorMessage']);
		}	

		$this->tpl->assign('language', $_SESSION['lang_folder']);
		$this->tpl->assign('request_uri', HOST.addEndSlash(MAIN_MODULE_URL) );
		$this->tpl->assign('login_uri', HOST.'login/');
		$this->tpl->assign('action', HOST.'login/');
		
		return $this;
	}
	

}
?>