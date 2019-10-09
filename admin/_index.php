<?php
define('MAIN_DIR', substr(dirname(__FILE__), 0, strpos(dirname(__FILE__), 'admin')-1 ) );

include "../includes/start.php";
include "../includes/classes/admin.class.php";
include DIR_TOOLS.'fckeditor/fckeditor.php';
include DIR_FUNCTIONS.'prepare_image.php';

// проверка логина 
$User = new User($db, false);

if (!isset($_SESSION['Login']) || !isset($_SESSION['DocumentId']) || ($_SESSION['DocumentId'] != ADMIN_DOC) )
	if ( !$User->Login(ADMIN_DOC) )
		header("Location:".HOST."admin/login");
if (!isset($_SESSION['Login']) || !isset($_SESSION['DocumentId']) || ($_SESSION['DocumentId'] != ADMIN_DOC) ) return "";

$p = array('prefix'=>'.');
$P = new Params($db, $p);
$P->LoadParams();

// индекс модуля в массиве запроса
if (MULTILANGUAGE === 0) define('MODULEINDEX',1);
else define('MODULEINDEX',2);
	
// / проверка логина 
$p=null;
$L = new Language($db, $p);
$L->ChangeLanguage();

$P->LoadParamsForLang();

$Doc = new Admin($db, ADMIN_DOC);

$Doc->LoadBoxes();

$Doc->LoadModule( isset($_GET['module']) ? $_GET['module'] : "" );
	
$Doc->AssignBoxes();
  
require MAIN_DIR."/admin/html_header.php";
 
$Doc->Display();

require MAIN_DIR."/admin/html_footer.php";

// лог sql запросов LOGGED  = true ведение лога запросов
if (LOGGED) $db->queryLog();
?>