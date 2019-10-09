<?php
include_once(DIR_MODULES."Message.php");

class Contacts extends Module
{
	private $SendResult = true;
	
	public function __construct(&$db, &$params) 
	{
		$this->table = "contacts";

		parent::__construct($db, $params);
		//$this->selected_action=array(0=>"-- Выберите действие --", "disable"=>"Отключить", "enable"=>" Включить", "deleting"=>" Удалить");
	}
		
	public function LoadBoxContent()
	{
		/*$cityUrl = end($this->query);
		$this->tpl->assign('PHONE', PHONE1);
		$this->tpl->assign('HOT_LINE', PHONE2);
		$curCity = substr(HOST,0,strlen(HOST)-1).$_SERVER['REQUEST_URI'];
	
		$this->tpl->assign_by_ref('list', select_list($this->getCityList(), 'city', 'onChange="document.location.href=this.value"', $curCity));
		*/

		$this->tpl->assign('language',$_SESSION['lang_folder']);
		return $this;
	}
		
	public function LoadContent()
	{
		// вывод контента страницы заданной в базе данных
		if (isset($this->Params['text']))
		{
			$this->tpl->assign('text', stripslashes($this->Params['text']));
			$this->tpl->assign('title', $this->Params['caption']);
		}

		// форма отправки сообщения
		$params = array('title'=>'send_message', 'dialog'=>false, 'toaddress'=>'adminmail', 'subject'=>ADMIN_SUBJECT);
		
		// успешная отправка с формы индивидуальный подбор программы
		if ( isset($this->query[4]) && $this->query[4] =='successfull' ){ 
			$params['dialog'] = true;
			$params['successfull'] = true;
		}
		$required = array('fio'=>'*', 'email'=>'*', 'phone'=>'*', 'subject'=>'*', 'message'=>'*');
		$this->tpl->assign('sendingform', $this->GetForm($params, $required));
		
		$this->Content = $this->fetchTemplate(CURRENT_TEMPLATE."modules/".$this->Params['template'], $this->tpl);
		
		//$this->GetNavigator();
		
		unset ($tpl);
	}

	//вывод формы и отправка e-mail
	private function GetForm($params, $required)
	{
		$tpl = new Template;
		$tpl->assign('successful', false);
		
		if (isset($_POST['send_mail']))
		{
			$secure_img = new Securimage();
			if (isset($_POST['captest']))
				$valid = $secure_img->check($_POST['captest']);
			else $valid = false;		
			
			if ($valid)
			{
				$this->sendMail();
				$params['successfull'] = true;
			}
			else
			{
				$tpl->assign('error', "{#captcha_error#}") ;
				$tpl->assign('fio', $_POST['fio']);
				$tpl->assign('email', $_POST['email']);
				$tpl->assign('phone', $_POST['phone']);
				$tpl->assign('subject', $_POST['subject']);
				$tpl->assign('message', $_POST['message']);
				$tpl->assign('toaddress', $_POST['toaddress']);
			}
		}

		//результат успешной отправки письма
        if (isset($params['successfull'])) {
				// страница результата выбирается по id = id текущего языка
				$page = $this->db->db_get_array("SELECT * FROM pages WHERE id = ".($_SESSION['lang_id']+1));
				
				$tpl->assign('successful', "<h1>".$page['title']."</h1>".stripslashes($page['text']));
				
				if (trim($page['meta_title']) !='')	$this->MetaTitle = $page['meta_title'];
				if (trim($page['meta_keywords']) !='')	$this->MetaTitle = $page['meta_keywords'];
				if (trim($page['meta_description']) !='')	$this->MetaTitle = $page['meta_description'];			
		}
		
			if (isset($_POST['subject']) && trim($_POST['subject']) == '') 
				$tpl->assign('subject', $params['subject']);		
			if (isset($_POST['toaddress']) && trim($_POST['toaddress']) == '')
				$tpl->assign('toaddress', $params['toaddress']);		
			$tpl->assign('required', $required);
			$tpl->assign('toaddress', $params['toaddress']);
			$tpl->assign('action', langUrl($_SESSION['lang_url']).$this->module_url.'/send/');
			$tpl->assign('language', $_SESSION['lang_folder']);
			$tpl->assign('captcha', get_captcha(DIR_CAPTCHA.'securimage/securimage_show.php'));
			$tpl->config_load($_SESSION['lang_folder']."/captions.cfg", 'contacts_form');
			$caption = $tpl->get_config_vars();
			$tpl->assign('form_title', $caption[$params['title']]);
			$tpl->assign('dialog', $params['dialog']);
		
		return  $this->fetchTemplate(CURRENT_TEMPLATE."modules/contact_form.html", $tpl);

	}
	
	public function getOrderForm()
	{
		$params = array('title'=>'send_order', 'dialog'=>true, 'toaddress'=>'email', 'subject'=>ORDER_SUBJECT);
		$required = array('fio'=>'*', 'email'=>'*', 'phone'=>'*', 'subject'=>'*', 'message'=>'*');
		return $this->GetForm($params, $required);
	}
	

	private function sendMail()
	{
		$toaddress = ADMIN_EMAIL;
		if (isset($_POST['toaddress']) && $_POST['toaddress']== 'email')
			$toaddress = EMAIL;
	
		$body = $_POST['message']."\n".$_POST['fio']."\nтел.:".$_POST['phone']."\ne-mail:".$_POST['email']."\nSended from ".$_SERVER['HTTP_REFERER'];

		$p = array('Name' => 'Message');
		$message = new Message($this->db, $p);
		$_POST['sended'] = 1;
			
		$message->save($_POST);
		send_mail($toaddress, $_POST['email'], $_POST['subject'], $body);
	}


	public function call($data)
	{
			if (!isset($_POST['message']))
				$_POST['message'] = ORDER_SUBJECT;
			$_POST['subject'] = ORDER_SUBJECT;
		
			$this->sendMail();
			die('ok');
	}
	
}
?>
