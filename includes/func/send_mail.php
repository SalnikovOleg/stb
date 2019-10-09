<?php
function send_mail($to_mail, $from_mail, $subject, $body, $ishtml = false)
{
	$subject = '=?koi8-r?B?'.base64_encode(utf2str($subject, "k")).'?=';
	
	if (strpos($to_mail, ',') !== false)
		$mails = explode(',', $to_mail);
	else
		$mails = explode(';', $to_mail);

	$ishtml?$header = "From: " . $from_mail. "\nContent-Language: ru \nContent-Type:text/html;  charset=utf-8 \nContent-Transfer-Encoding: 8bit" :
        $header = "From: " . $from_mail. "\nContent-Language: ru \nContent-Type:text/plain;  charset=utf-8 \nContent-Transfer-Encoding: 8bit";
	foreach ($mails as $mail)	
		@mail($mail, $subject, $body, $header);
}
?>