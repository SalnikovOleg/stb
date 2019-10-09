<?php
if (!function_exists('send_mail')){
  include DIR_INCLUDES.'func/send_mail.php';
}

function ask_question() {
  $subject = 'Вопрос с боковой кнопки сайта';
  $body = "Имя : ". substr($_POST['name'],0, 20)."\n";
  $body .= "Тел.: " . substr($_POST['phone'],0, 20)."\n";
  $body .= "E-mail : ". substr($_POST['email'],0,30)."\n";
  $body .= "Вопрос : ". htmlspecialchars($_POST['question'])."\n";
  $from_email = substr($_POST['email'],0,30);
  $to_mail =  'zapros@studybridge.com.ua';

  send_mail($to_mail, $from_email, $subject, $body);

//    file_put_contents('fff.txt', var_export($db, true));
      $webinar_subject= "Ваш успешно отправлен";

// $webinar = $db->fetch_row('select `webinar_info` from articles_pages where id =  '.$id_page);
//    file_put_contents('fff.txt', var_export($webinar, true),8);
  send_mail($from_email, $to_mail, $webinar_subject, $body);
  echo 'ok';
}
?>