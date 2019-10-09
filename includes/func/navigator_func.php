<?php
function get_cat_navi($path, $table)
{
   if ($path=='') return '';
   $result='';
   $idstr = str_replace('_',',',$path);

   $list= db_get_list('select `id`,`name` from `'.$table.'` where `id` in ('.$idstr.') order by `id`','id','name');
   if ($list != null)	
	foreach ($list as $key=>$value)
	  $result.=$value.' :: ';

   return substr($result,0,strlen($result)-3) ;
}

function page_navigator($page, $record_count, $once_count)
{
	$res='';
	if ($once_count==0) $once_coun = 1;
	$total_pages=(integer)($record_count/$once_count);
	if ($record_count%$once_count>0) $total_pages=$total_pages+1;
	
	if($total_pages==1 || $total_pages==0)	return;

	$list_once=4;

	if($total_pages<=$list_once)
	{
		$p_begin=0;
		$p_end=$total_pages-1;
	}
	else
	{
		$p_begin=$page-(integer)($list_once/2);
		$p_end=$p_begin+$list_once;

		if($p_begin<0)
		{
			$shift=-$p_begin;
			$p_begin=0;
			$p_end=$p_end+$shift;
			$flag1=true;
		}
	
		if($p_end>$total_pages-1)
		{
			$shift=$p_end-$total_pages+1;
			$p_begin=$p_begin-$shift;
			$p_end=$total_pages-1;
			$flag2=true;
		}
	}
		
	$res=("<table class=\"pages\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\"><tr><td> ");
	if($page!=0)
		{
			$res.=("<td><a href=\"javascript:void(0);\"
			     onClick=\"getElementById('pageno').value=0; navigator_form_submit(0);\" >
			     << </a>
			     </td>");
		}
	for($i=$p_begin;$i<=$p_end;$i++)
		{
			if($i!=$page)
				$res.=("<td><a href=\"javascript:void(0);\"
			     onClick=\"getElementById('pageno').value=".$i."; navigator_form_submit(".$i.");\" >
				 ".($i+1)."</a>
				 </td>");
			else
				$res.=("<td class=\"active\">".($i+1)."</td>");
		}
	if($page!=$total_pages-1)
		{
			$res.=("<td><a href=\"javascript:void(0);\"
			     onClick=\"getElementById('pageno').value=".($total_pages-1)."; navigator_form_submit(".($total_pages-1).");\" >
				>> </a>
				</td>");
		}
	$res.=("</tr></table>");

return $res;
}


function create_navigator_form($table='', $whereclause, $url)
{
 
  include ('admin/lang/'.$_SESSION['language_directory'].'/lists.php');
  
  if (isset($_POST['once_record_count'])) $_SESSION['once_record_count'] =  $_POST['once_record_count'];
  elseif (!isset($_SESSION['once_record_count'])) $_SESSION['once_record_count']= ONCE_RECORD_COUNT;
  $once_record_count=$_SESSION['once_record_count'];
		
  if (!isset($_POST['pageno']) || (trim($_POST['pageno']) == '')) $_POST['pageno']=0;
  $start_items=$once_record_count*($_POST['pageno']);

  if ($table!='')  $record_count=db_get_value('select count(*) from '.$table.$whereclause);
  else
  {
  	$records=db_dataset_array($whereclause);
  	$record_count = count($records);
  }
  
  $tpl=new Template;
  $tpl->assign('language',$_SESSION['language_directory']);
  $tpl->assign('url',$url);
  $tpl->assign('items_onpage', $items_onpage);
  $tpl->assign('once_record_count', $once_record_count);
  
  if ($record_count>$once_record_count)
   	$tpl->assign('page_navigator',page_navigator($_POST['pageno'], $record_count, $once_record_count));
  else
    $tpl->assign('page_navigator',false);
    
  $page_navigator_form=$tpl->fetch(CURRENT_TEMPLATE.'/modules/navigator.html');
  unset ($tpl);
 
  $result=array('page_navigator'=>$page_navigator_form, 'record_count'=>$record_count, 'start_items'=>$start_items,  'once_record_count'=>$once_record_count);

  return $result;
}



?>
