<?php
function langUrl($url)
{
	if ($url == '')	return '';
	else return $url.'/';
}

function addEndSlash($value)
{
	if ( strpos($value, ".".DEFAULT_EXT) == false  &&  strpos($value, '/') != strlen($value))
		$value .='/';
		
	return $value;	
}

// cоздать список ссылок
function create_href_list(&$dataset, $current_url, $lang_url, $ext)
{
	for ($i=0; $i<=count($dataset)-1; $i++)
	{
		$href = create_href($dataset[$i]['url'], langUrl($lang_url), $ext);

		if ($current_url == $href) $current = 1; else $current = 0;

		$result[]=array('name'=>$dataset[$i]['name'], 'href'=>$href, 'current'=>$current);
	}
	
	if (!isset($result)) $result=null;
	return $result;
}

//сформировать ссылку
function create_href($url, $lang, $ext)
{
	if (($url != null) && (trim($url) != '')) 
		if (strpos($url,'https://')>0) 
		 	$href = $url;
		else $href = HOST.$lang.$url;
	else $href = HOST.$lang;
	
	if (strpos($href,".".$ext) === false) 
		if ($href[strlen($href)-1] !="/" ) $href .="/";	
	
	return $href;	
}

//Создание дерева 
//датасет должен иметь поля id, parent, node, url, name, description
function create_tree($cId, $level, $dataset, &$tree)
{
	if ($cId != 0)
	{
		$tree[$cId] = $dataset[$cId];
		$tree[$cId]['url'] = create_href($dataset[$cId]['url'], $_SESSION['lang_url']."/", DEFAULT_EXT);
		$tree[$cId]['level'] = $level; 
	}
	
	foreach ($dataset as $key => $value)
	{
		if ($value['parent'] == $cId)
			create_tree($key, $level+1, $dataset, $tree);
	}
}

function createVideoUrl($url)
{
	if (strpos($url, 'youtube.com') == false) return $url;
	
	if (strpos($url, 'https') !== false){
		$sp = strpos($url, ':');
		$protocol = substr($url, 0, $sp+3);
		$u = substr($url, $sp+3);
	}
	else{
		$protocol = 'https://';
		$u = $url;
		$url .= $protocol;
	}
	
	$t = explode('/',$u);

	if ( isset($t[1]) && $t[1]== 'v') return $url;
	
	$domain = $t[0];	

	$vpos = strpos($t[1], 'v=')+2;

	$epos = strpos($t[1], '&', $vpos);
	if (!$epos) $epos = strlen($t[1]);

	$videoid = substr($t[1], $vpos, $epos-$vpos); 

	$url = $protocol.$domain.'/v/'.$videoid;
	
	return $url; 
}
?>
