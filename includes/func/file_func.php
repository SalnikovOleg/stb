<?php
//-----------------                   upload File                     -------------------------
function upload_file(&$FILES, $path, $name)
{
	$filename=check_exist_file($path, $FILES[$name]['name']);
	$destination=$path.$filename;
	$temp=$FILES[$name]['tmp_name'];
	move_uploaded_file($temp, $destination);
	return $filename;	
}


// загрузка изображения
function img_upload(&$data, $folder, $pre="", &$uploaded=false)
{
	$img = "";
	if (!isset($_FILES[$pre.'image']['name']) || $_FILES[$pre.'image']['name']=='')	
		if (!isset($data[$pre.'listimage']) || ($data[$pre.'listimage']=='') || ($data[$pre.'listimage']=='0')) 
			if ($data[$pre.'oldimage'] =='') $img='';
			else $img = $data[$pre.'oldimage'];
		else $img = $data[$pre.'listimage'];
	else 
	{
		$img=upload_file($_FILES, $folder, $pre.'image');
		$uploaded = true;
	}	
	
	return $img;	
}

// загрузка изображения  и создание уменьшенного изображения
function img_thumbs_upload(&$data, $folder, $thumbs, $w, $h, $pre="")
{
	$img = "";
	if (!isset($_FILES[$pre.'image']['name']) || $_FILES[$pre.'image']['name']=='')	
		if (!isset($data[$pre.'listimage']) || ($data[$pre.'listimage']=='') || ($data[$pre.'listimage']=='0')) 
			if ($data[$pre.'oldimage'] =='') $img='';
			else $img = $data[$pre.'oldimage'];
		else $img = $data[$pre.'listimage'];
	else 
	{
		$img=upload_file($_FILES, $folder, $pre.'image');
		resize_image($folder.$img, $thumbs.$img, $w, $h);
	}	
	
	return $img;	
}

	
//-----------------                  check exist file                     -------------------------
function check_exist_file($path, $file)
{
  $file = translite($file);
  $i=0;
  while (file_exists($path.$file))
	{
	  $i++;
	  $filename=substr($file,0,strrpos($file,'.'));
	  if(substr($file,0,strrpos($filename,'['))!="") $filename=substr($file,0,strrpos($filename,'['));
	  $fileext=strrchr($file,'.');
	  $file=$filename."[".$i."]".$fileext;
	}	
  return $file;
}

function try_mkdir($path, $dir)
{
   $i=0;
   $result = $dir;
   while (!file_exists($path.$result))
   { 
   	 $i++;
   	 $result = $dir.'['.$i.']';
   }
   return $result;
}

//-----------      создание директории
function create_dir($dir)
{
	if (!file_exists($dir))
		return mkdir($dir);
	else	
		return true;	
}

function write_to_file($file, $text)
{
	if (!file_exists($file))
		$f = fopen($file, "w");
	else 
		$f = fopen($file, "a");
		
	fwrite($f, $text."\n");
	fclose($f);
}

function read_file($file)
{
	$text = "";
	$rows = file($file);
	foreach($rows as $val)
		$text .=$val;
	
	return $text;
}

function select_loaded_files($path, $name, $attr, $selected_value='', $selected_index=1, $default_str='--- Выберите из загруженых ---')
{
 $i=0;	
 
 $result='<select class="select" id="'.$name.'" name="'.$name.'" '.$attr.'>
 				<OPTION value="0" selected>'.$default_str;
 if( $dir=@opendir($path))
    while (false!==($file=readdir($dir)))
	 if( !(($file=='.')||($file=='..')) )
      {
	    $i++;  
	    if ($selected_value!='')
     	  if ($selected_value == $file)   
			$result.='<OPTION value="'.$file.'" selected>'.$file;
		  else  	
		  	$result.='<OPTION value="'.$file.'">'.$file;
		else
		 if ($i == $selected_index)  
			$result.='<OPTION value="'.$file.'" selected>'.$file;
		  else	
			$result.='<OPTION value="'.$file.'">'.$file;

      }
      
$result.="</SELECT>";		
 return $result;   	
 }
 
 
function get_file_list($path)
{
 $result=array();	
 if( $dir=@opendir($path))
    while (false!==($file=readdir($dir)))
	 if( !(($file=='.')||($file=='..')) )
      	$result[$file]=$file;
 sort($result); 
 return $result;
}

function write_log($file, $text)
	{
		$name ='./upload/'.$file.'_'.date("Y-m-d").'.log';
		$f = fopen($name,"a");
		fwrite($f, $text."\n");
		fclose($f);
	}

function file_del($mask)
{
	if (file_exists($mask))
		unlink($mask);
	return;
	
	$files = glob($mask, GLOB_NOSORT);
	if (count($files)>0) {
		foreach ($files as $file)
			if (file_exists($file))
				unlink($file);
	}			
}

function folderDelete($obj) {
	if(is_file($obj))
		unlink($obj);
	else{
		$cat = glob($obj."/*");
		if (is_array($cat) && count($cat)>0)
			foreach($cat as $o) 
				folderDelete($o);
		if (file_exists($obj))	
			rmdir($obj);
	}
}	

?>