<?php
function get_post_session_value($name, $default) {
		if (isset($_POST[$name])) {
			$_SESSION[$name] = $_POST[$name];
		}
		elseif (!isset($_SESSION[$name])) {
			$_SESSION[$name] = $default;
		}	
	return 	$_SESSION[$name];
}

function get_post_value($name, $default) {
		if (isset($_POST[$name])) 
			return $_POST[$name];
		elseif( isset($_GET[$name]))
			return $_GET[$name];
		else
			return $default;
}
?>
