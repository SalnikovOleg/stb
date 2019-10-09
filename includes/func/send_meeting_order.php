<?php
if (!function_exists('send_mail')){
  include DIR_INCLUDES.'func/send_mail.php';
}

function send_meeting_order()
{
	$body = "Имя: ".(isset($_POST['name'])?$_POST['name']:$_POST['fio'])."\n";
	$body .= "Тел.: ".$_POST['phone']."\n";
	$body .= "E-mail: ".(isset($_POST['mail'])?$_POST['mail']:$_POST['email'])."\n";
	if (isset($_POST['age'])) {
	    $body .= "Возраст: ".$_POST['age']."\n";
	    $body .= "Недель ".$_POST['school']."\n";
	}
	
	if (count($_POST['country']) > 0){
	$body .="Страна обучения: \n";
	foreach ($_POST['country'] as $item)
		$body .= $item." ,";
	}
	$body .="\n";
	if (count($_POST['program']) > 0){
	$body .="Язык обучения: \n";
	foreach ($_POST['program'] as $item)
		$body .= $item." ,";
	}
		
	if (isset($_POST['message']) ) {
	    $body .= "\n".$_POST['message'];
	}
	
	$from_mail = isset($_POST['mail'])?$_POST['mail']:$_POST['email'];
	$subject = ORDER_SUBJECT;
	
	$to_mail =  EMAIL;

	send_mail($to_mail, $from_mail, $subject, $body);
}
?>