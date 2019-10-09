<?php
  //define('HOST_NAME', 'http://localhost');
  
  define('HOST', 'https://'.$_SERVER['SERVER_NAME'].'/');
  define('HOST_ADMIN', HOST.'admin/index.php');
  define('DIR_INCLUDES', MAIN_DIR.'/includes/');
  define('DIR_FUNCTIONS', DIR_INCLUDES.'func/');
  define('DIR_CLASSES', DIR_INCLUDES.'classes/');
  define('DIR_MODULES', DIR_INCLUDES.'modules/');
  define('DIR_IMAGES', './upload/image/');
  define('DIR_THUMBS', DIR_IMAGES.'thumbs/');
  define('DIR_TOOLS', MAIN_DIR.'/tools/'); 
  define('DIR_LANGUAGE', DIR_INCLUDES.'lang/');
  define('DIR_DOWNLOAD', 'out/');
  define('DIR_CAPTCHA', './tools/');
  
  define('DIR_IMG_COUNTRY', '../upload/image/country/');
  
  require_once("db_tables.php"); 
 
  define('LOGGED', false); 	// логировать sql запросы  true
  define('ADMIN_DOC', 2);  
?>