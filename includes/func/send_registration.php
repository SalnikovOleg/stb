<?php
if (!function_exists('send_mail')){
  include DIR_INCLUDES.'func/send_mail.php';
}

function send_registration() {
  $subject = 'Заявка на вебинар';
  $body = "Имя : ". substr($_POST['fio'],0, 20)."\n";
  $body .= "Тел.: " . substr($_POST['phone'],0, 20)."\n";
  $body .= "E-mail : ". substr($_POST['email'],0,30)."\n";
    $id_page = $_POST['id_page'];
  $from_email = substr($_POST['email'],0,30);  
  $to_mail =  REGISTRATION_EMAIL;

  send_mail($to_mail, $from_email, $subject, $body, true);

    $db = new DB_MySql();
//    file_put_contents('fff.txt', var_export($db, true));
   $webinar = $db->db_get_value('select `webinar_info` from `articles_pages` where `id` =  '.$id_page);
   $webinar_subject = $db->db_get_value('select `webinar_theme` from `articles_pages` where `id` =  '.$id_page);
   empty($webinar_subject) || is_null($webinar_subject) || $webinar_subject == ""?
       $webinar_subject= "Вы успешно подписались на семинар на сайте: http://studybridge.com.ua":$webinar_subject;

// $webinar = $db->fetch_row('select `webinar_info` from articles_pages where id =  '.$id_page);
//    file_put_contents('fff.txt', var_export($webinar, true),8);
  send_mail($from_email, $to_mail, $webinar_subject, $webinar, true);
    $db->db_close();





  echo 'ok';
}
?>