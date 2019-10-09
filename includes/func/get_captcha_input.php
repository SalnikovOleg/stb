<?php
// функция возвращает часть формы для ввода текста с рисунка
function get_captcha($captcha_module, $width="100")
{	// передавать путь и имя модуля 
 return "Код с картинки <input class=\"captest\" type=\"text\" id=\"captest\" name=\"captest\" value=\"\">
  <img src=\"".$captcha_module."\" id=\"image\" align=\"absmiddle\" width=\"".$width."\">";
}	
?>