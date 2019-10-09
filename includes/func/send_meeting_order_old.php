<?php
if (!function_exists('send_mail')){
  include DIR_INCLUDES.'func/send_mail.php';
}

function send_meeting_order()
{
	$body = "Имя: ".$_POST['name']."\n";
	$body .= "Тел.: ".$_POST['phone']."\n";
	$body .= "E-mail: ".$_POST['mail']."\n";
	$body .= "Возраст: ".$_POST['age']."\n";
	$body .= "Место учебы: ".$_POST['school']."\n";
	
	if (count($_POST['country']) > 0){
	$body .="Страна: \n";
	foreach ($_POST['country'] as $item)
		$body .= $item.", ";
	}
	
	if (count($_POST['program']) > 0){
	$body .="\nПрограма: \n";
	foreach ($_POST['program'] as $item)
		$body .= $item.", ";
	}
		
	$from_mail = $_POST['mail'];
	$subject = 'Зказа индивидуальной встречи';
	
	$to_mail =  EMAIL;

	send_mail($to_mail, $from_mail, $subject, $body);
}
?>
