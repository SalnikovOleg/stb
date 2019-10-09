<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<title><?php echo $Doc->MetaTitle; ?></title>
<base href="<?php echo  HOST; ?>"/>
<meta http-equiv="Content-Style-Type" content="text/css; charset=utf-8" />
<link rel="stylesheet" type="text/css" href="<?php echo HOST.'admin/templates/index.css'; ?>" />
<link rel="stylesheet" type="text/css" href="<?php echo HOST.'admin/templates/dropdown.css'; ?>" />
<link rel="stylesheet" type="text/css" href="<?php echo HOST.'includes/jscript/jquery/plugins/thickbox/thickbox.css'; ?>" />
<!--[if lte IE 7]><link rel="stylesheet" type="text/css" href="<?php echo HOST.'admin/templates/ie.css'; ?>" /><![endif]-->
<script type="text/javascript" src="<?php echo HOST.'includes/jscript/jquery/jquery.min.js'; ?>"></script>
<script type="text/javascript" src="<?php echo HOST.'includes/jscript/jquery/plugins/Dropdowns/js/jquery.dropdownPlain.js'; ?>"></script>
<script type="text/javascript" src="<?php echo HOST.'includes/jscript/main.js'; ?>"></script>
<script type="text/javascript" src="<?php echo HOST.'includes/jscript/admin.js'; ?>"></script>
<script type="text/javascript" src="<?php echo HOST.'includes/jscript/calendar.js';?>"></script>
<script type="text/javascript" src="<?php echo HOST.'includes/jscript/jquery/plugins/thickbox/thickbox.js';?>"></script>
<script type="text/javascript" src="<?php echo HOST.'includes/jscript/swfobject/swfobject.js';?>"></script>
<script type="text/javascript" src="<?php echo HOST.'includes/jscript/loadVideo.js';?>"></script>
<script type="text/javascript" src="/tools/tiny_mce/tiny_mce.js"></script>


<?php
/*
<script>
$(document).ready(function() { 
	$('a[name=modal]').click(function(e) {
		e.preventDefault();
		var id = $(this).attr('href');
		var maskHeight = $(document).height();
		var maskWidth = $(window).width();
		$('#mask').css({'width':maskWidth,'height':maskHeight});
		$('#mask').fadeIn(1000);
		$('#mask').fadeTo("slow",0.8);
		var winH = $(window).height();
		var winW = $(window).width();
		$(id).css('top', winH/2-$(id).height()/2);
		$(id).css('left', winW/2-$(id).width()/2); $(id).fadeIn(2000);  
	});
	$('.window .close').click(function (e) {e.preventDefault();$('#mask, .window').hide();});
	$('#mask').click(function () {$(this).hide();$('.window').hide();});
});
</script>
*/
?>
<script type="text/javascript">
$(document).ready(function(){
	$("ul.tabs li").click(function(){
		var c = $(this).attr('class');
		if (c.indexOf("current") >= 0) return;
		$(".box").animate({ opacity: "hide" }, "fast");
		$("div."+c).animate({ opacity: "show" }, "fast");
		$(this).addClass('current').siblings().removeClass('current');
	});
});
</script>

<script type="text/javascript">
tinyMCE.init({
	mode : "",
	editor_deselector : "notinymce",
	theme : "advanced",
	language : "ru",
	paste_create_paragraphs : false,
	paste_create_linebreaks : false,
	paste_use_dialog : true,
	convert_urls : false,
	
	plugins : "safari,spellchecker,pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,fullscreen,noneditable,visualchars,nonbreaking",

	file_browser_callback : "ajaxfilemanager",
	elements : "ajaxfilemanager",

	spellchecker_languages : "+Russian=ru,English=en",
	spellchecker_rpc_url : "https://<?=$_SERVER['HTTP_HOST']?>/tools/tiny_mce/plugins/spellchecker/rpc_proxy.php",

	// Theme options
	theme_advanced_buttons1 : "save,newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,styleselect,formatselect,fontselect,fontsizeselect",
	theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,help,code,|,insertdate,inserttime,preview,|,forecolor,backcolor",
	theme_advanced_buttons3 : "tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,emotions,iespell,media,advhr,|,print,|,ltr,rtl,|,fullscreen",
	theme_advanced_buttons4 : "insertlayer,moveforward,movebackward,absolute,|,styleprops,spellchecker,|,cite,abbr,acronym,del,ins,attribs,|,visualchars,nonbreaking,template,blockquote,pagebreak,|,insertfile,insertimage",
	theme_advanced_toolbar_location : "top",
	theme_advanced_toolbar_align : "left",
	theme_advanced_statusbar_location : "bottom",
	theme_advanced_resizing : true

});

  function ajaxfilemanager(field_name, url, type, win) {
    var ajaxfilemanagerurl = "https://<?=$_SERVER['HTTP_HOST']?>/tools/tiny_mce/plugins/ajaxfilemanager/ajaxfilemanager.php?language=ru";
    switch (type) {
      case "image":
        break;
      case "media":
        break;
      case "flash":
        break;
      case "file":
        break;
      default:
        return false;
    }
    tinyMCE.activeEditor.windowManager.open({
      url: ajaxfilemanagerurl,
      width: 782,
      height: 440,
      inline : "yes",
      close_previous : "no"
    },{
      window : win,
      input : field_name
    });
  }
  
  tinyMCE.execCommand("mceAddControl", false, 'text');
  tinyMCE.execCommand("mceAddControl", false, 'text2');
function toggleHTMLEditor(id) {
	if (!tinyMCE.get(id))
		tinyMCE.execCommand("mceAddControl", false, id);
	else
		tinyMCE.execCommand("mceRemoveControl", false, id);
}

</script>

</head>
<body>
