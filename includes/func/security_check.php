<?php
function security_check()
{
	if (!isset($_SESSION['Login']) || !isset($_SESSION['Password']))
		header("Location:".HOST.'login/');	
}
?>