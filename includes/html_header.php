<!DOCTYPE html>
<html>
<head>
	<title><?php echo strip_tags($Doc->MetaTitle); ?></title>
	<meta name="description" content="<?php echo $Doc->MetaDescription; ?>" />
	<meta name="keywords" content="<?php echo $Doc->MetaKeywords; ?>" />
	<meta charset="utf-8">
	<base href="<?php echo  HOST; ?>"/>

	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<script src="https://code.jquery.com/jquery-latest.js"></script>
	<!--script type="text/javascript" src="<?php echo 'http://'.HOST_NAME;?>/includes/jscript/jquery/jquery-1.11.0.min.js"></script-->
	<!--script src="http://code.jquery.com/jquery-migrate-1.2.1.js"></script!-->
	<!--script src="http://code.jquery.com/mobile/1.4.5/jquery.mobile-1.4.5.min.js"></script-->

	<!-- Bootstrap -->
        <link rel="stylesheet" type="text/css" href="<?php echo 'https://'.HOST_NAME;?>/bootstrap/css/bootstrap.css" />
        <script type="text/javascript" src="<?php echo 'https://'.HOST_NAME;?>/bootstrap/js/bootstrap.min.js"></script>
	<!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
	<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
	<!--[if lt IE 9]>
		<script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
		<script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
	<![endif]-->

	<link rel="stylesheet" type="text/css" href="<?php echo 'https://'.HOST_NAME.'/templates/'.CURRENT_TEMPLATE.'css/main.css'; ?>" />
	<link rel="stylesheet" type="text/css" href="<?php echo 'https://'.HOST_NAME.'/templates/'.CURRENT_TEMPLATE.'css/style.css'; ?>" />
	<link rel="stylesheet" type="text/css" href="<?php echo 'https://'.HOST_NAME.'/includes/jscript/jquery/plugins/thickbox/thickbox.css'; ?>" />
	<link rel="stylesheet" type="text/css" href="<?php echo 'https://'.HOST_NAME.'/templates/'.CURRENT_TEMPLATE.'css/'.$Doc->StyleSheet; ?>" />
	<?php $Doc->linkStyleSheet(); ?>
	<!--[if lt IE 9]><link rel="stylesheet" type="text/css" href="<?php echo 'https://'.HOST_NAME.'templates/'.CURRENT_TEMPLATE.'css/ie6.css'; ?>" /><![endif]-->

	<!--script type="text/javascript" src="<?php echo 'https://'.HOST_NAME;?>/includes/jscript/jquery/jquery-1.4.2.js"></script-->
	<script type="text/javascript" src="<?php echo 'https://'.HOST_NAME;?>/includes/jscript/main.js?1"></script>
	<script type="text/javascript" src="<?php echo 'https://'.HOST_NAME;?>/includes/jscript/jquery/plugins/thickbox/thickbox.js" ></script>
	<script type="text/javascript" src="<?php echo 'https://'.HOST_NAME;?>/includes/jscript/swfobject/swfobject.js"></script>
	<script type="text/javascript" src="<?php echo 'https://'.HOST_NAME;?>/includes/jscript/loadVideo.js?2"></script>
	<script type="text/javascript" src="<?php echo 'https://'.HOST_NAME;?>/includes/jscript/slider.js"></script>
	<script type="text/javascript" src="<?php echo 'https://'.HOST_NAME;?>/includes/jscript/responsiveCarousel.min.js"></script>

	<script type="text/javascript" src="<?php echo 'https://'.HOST_NAME;?>/includes/jscript/jquery/jquery.simplemodal.js"></script>
	<script type="text/javascript" src="<?php echo 'https://'.HOST_NAME;?>/includes/jscript/init.js"></script>
	<script type="text/javascript" src="<?php echo 'https://'.HOST_NAME;?>/includes/jscript/appruv.js"></script>
	<script type="text/javascript" src="<?php echo 'https://'.HOST_NAME;?>/includes/jscript/ask.js"></script>


	<script type="text/javascript">
	var CLOSING_DIALOG = <?php echo CLOSING_DIALOG;?>;
	$(document).ready(function(){
		$('#order').click(function(){ 
			var left = $(document).width()/2-$('#dialog').outerWidth(true)/2;
			$('#dialog').load('ajax.php?module=Contacts&method=getOrderForm').css({'display':'block','left':left}); 
		});
		$('#callback').click(function(){ 
			var left = $(document).width()/2-$('#dialog').outerWidth(true)/2;
			$('#dialog').load('ajax.php?module=Contacts&method=getOrderForm').css({'display':'block','left':left}); 
		});
		
	});
	</script>

	<!-- Global site tag (gtag.js) - Google Analytics -->
	<script async src="https://www.googletagmanager.com/gtag/js?id=UA-16860583-2"></script>
	<script>
	  window.dataLayer = window.dataLayer || [];
	  function gtag(){dataLayer.push(arguments);}
	  gtag('js', new Date());
	  gtag('config', 'UA-16860583-2');
	  gtag('config', 'AW-1004626098');
	</script>

</head>
<!--  oncontextmenu="return false;" oncopy="return false;" ondragstart="return false" onselectstart="return false" onselectstart="return false" -->
<body>