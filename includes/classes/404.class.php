<?php
class E404 extends Module
{
  private $error_details = "";
  
  function __construct($mess)
  {
	$this->error_details = $mess;
	header('HTTP/1.1 404 Not Found');
  }
  
  public function ItemsList(&$params){}
  
  public function LoadBoxContent()
  {
	return "";
  }
  
  public function LoadContent()
  {
	$error_tpl = new Template;
	
	$error_tpl -> assign('language',$_SESSION['lang_folder']);
	$error_tpl -> assign('HOST',HOST);
	$error_tpl -> assign('required_url', $_SESSION['REQUEST_URI']);
	$error_tpl -> assign('ADMIN_EMAIL', ADMIN_EMAIL);
  
	//$this->CSSFile = '404.css';

    if (file_exists('./templates/'.CURRENT_TEMPLATE.'/modules/404.html'))
		$this->Content = $error_tpl->fetch(CURRENT_TEMPLATE.'/modules/404.html');
	else
		{
			$error_tpl->config_load($_SESSION['lang_folder']."/captions.cfg",'404');
			$this->Content = $error_tpl->get_config_vars('default_message_begin')." "
					.HOST.substr($_SERVER['REQUEST_URI'],1)." "
					.$error_tpl->get_config_vars('default_message_end')
					.$error_tpl->get_config_vars($this->error_details);
			$error_tpl->clear_config();
		}

	unset($error_tpl);
  }
}  
?>