<?php
//phpinfo();
//ini_set("display_errors","1"); 
//ini_set("display_startup_errors","1"); 
//ini_set('error_reporting', E_ALL);



include 'securimage.php';

$img = new securimage();

$img->show(); // alternate use:  $img->show('/path/to/background.jpg');

?>
