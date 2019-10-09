<?php
function select_list(&$list, $name, $attr, $selected_value='', $selected_index=0, $default_str='', $empty_option=true)
{
 $result='<select class="select" id="'.$name.'" name="'.$name.'" '.$attr.'>';
 if ($empty_option)        
	$result .='<option value="" selected="selected">'.$default_str."</option>"; 			
 
 $i=-1;	
 if (count($list) > 0)
  foreach ($list as $key => $value)
   {
   	 $i++;
	 if ($selected_value!='')
     	 if ($selected_value == $key)   
			$result.='<option value="'.$key.'" selected="selected">'.$value."</option>";
		 else  	
		  	$result.='<option value="'.$key.'">'.$value."</option>";
	 else
		if ($i == $selected_index)  
			$result.='<option value="'.$key.'" selected="selected">'.$value."</option>";
		else	
			$result.='<option value="'.$key.'">'.$value."</option>";
   }
      
$result.="</select>";		
 return $result;   	
 }

function select_value_list(&$list, $name, $attr, $selected_value='', $selected_index=1, $default_str='')
{
 $result='<select class="select" id="'.$name.'" name="'.$name.'" '.$attr.'>
         <option value="0" selected="selected">'.$default_str."</option>"; 			
 $i=0;	
  if (count($list) > 0)
  foreach ($list as $key => $value)
   {
   	  $i++;
	 if ($selected_value!='')
     	   if ($selected_value == $value)   
			$result.='<option value="'.$value.'" selected="selected">'.$value."</option>";
		 else  	
		  	$result.='<option value="'.$value.'">'.$value."</option>";
	   else
		 if ($i == $selected_index)  
			$result.='<option value="'.$value.'" selected="selected">'.$value."</option>";
		 else	
			$result.='<option value="'.$value.'">'.$value."</option>";
   }
      
$result.="</select>";		
 return $result;   	
 }

 function select_empty($name, $attr = "", $default_str = "")
 {
	return '<select class="select" id="'.$name.'" name="'.$name.'" '.$attr.'>
         <option value="0" selected="selected">'.$default_str."</option>".
	"</select>"; 
 }
 
 // массив
 function java_array(&$list, $name, $key, $value)
 {
	$result ="<script> var ".$name." = {";
	foreach($list as $item)
		$result.= $item[$key]." : ".$item[$value].", ";
	$result = substr($result, 0, strlen($result)-2)." }</script>";

	return $result;
 }
?>