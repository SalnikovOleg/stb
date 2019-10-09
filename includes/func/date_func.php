<?php
function str_to_date($str)
{
	$date = explode(".", $str);
	if (count($date)==3)
	  return $date[2]."-".$date[1]."-".$date[0];
	else 
	{ 
		$date = explode("-", $str);
		if (count($date)==3) return $str;
		else return 'null';
	}	
}

function YMDToDMY($date)
{
	$dt = explode('-',$date);
	if (count($dt) == 3)
		return $dt[2].'.'.$dt[1].'.'.$dt[0];
	else 
		return $date;
}

function DMYToYMD($date)
{
	$dt = explode('.',$date);
	if (count($dt) == 3)
		return $dt[2].'-'.$dt[1].'-'.$dt[0];
	else 
		return $date;
}

function getMonth($date, $p='g')
{
	$monthg = array('1'=>'січня', '2'=>'лютого', '3'=>'березня', '4'=>'квітня', '5'=>'травня', '6'=>'червня', '7'=>'липня', '8'=>'серпня', '9'=>'вересня', '10'=>'жовтня', '11'=>'листопада', '12'=>'грудня');
	$monthn = array('1'=>'Cічень', '2'=>'Лютий', '3'=>'Березень', '4'=>'Квітень', '5'=>'Травень', '6'=>'Червень', '7'=>'Липень', '8'=>'Серпень', '9'=>'Вересень', '10'=>'Жовтень', '11'=>'Листопад', '12'=>'Грудень');

	$d = explode('.', $date);
	if (count($d) != 3)
		$d = explode('-', $date);

	if ($p == 'g')
		return $monthg[(int)$d[1]];
	if ($p == 'n')
		return $monthn[(int)$d[1]];
}

function DMYtoDMonthY($date)
{
	$d = explode('.', $date);
	return $d[0]." ".getMonth($date)." ".$d[2]." р.";
}

function YMDtoDMonthY($date)
{
	$month = array('1'=>'січня', '2'=>'лютого', '3'=>'березня', '4'=>'квітня', '5'=>'травня', '6'=>'червня', '7'=>'липня', '8'=>'серпня', '9'=>'вересня', '10'=>'жовтня', '11'=>'листопада', '12'=>'грудня');
	$d = explode('-', $date);
	return $d[2]." ".getMonth($date)." ".$d[0]." р.";
}

function strToDateTime($str)
{
	$date = explode(".", $str);
	if (count($date)==3)
	  return mktime(0,0,0, $date[1], $date[0], $date[2]);
	else 
	{ 
		$date = explode("-", $str);
		if (count($date)==3) 
			return mktime(0,0,0, $date[1], $date[2], $date[0]);
		else 
			return mktime();
	}	
}

function dateAdd($interval, $number, $date) 
{
	$dateTime = strToDateTime($date);
    $date_time_array = getdate($dateTime);
    $hours = $date_time_array['hours'];
    $minutes = $date_time_array['minutes'];
    $seconds = $date_time_array['seconds'];
    $month = $date_time_array['mon'];
    $day = $date_time_array['mday'];
    $year = $date_time_array['year'];

    switch ($interval) {
    
        case 'yyyy':
            $year+=$number;
            break;
        case 'q':
            $year+=($number*3);
            break;
        case 'm':
            $month+=$number;
            break;
        case 'y':
        case 'd':
        case 'w':
            $day+=$number;
            break;
        case 'ww':
            $day+=($number*7);
            break;
        case 'h':
            $hours+=$number;
            break;
        case 'n':
            $minutes+=$number;
            break;
        case 's':
            $seconds+=$number; 
            break;            
    }
       $timestamp= mktime($hours,$minutes,$seconds,$month,$day,$year);
    return $timestamp;
}

function daysBetween($date1, $date2)
{
	$dateTime1 = strToDateTime($date1);
	$dateTime2 = strToDateTime($date2);
	$dif = $dateTime2 - $dateTime1;
	return floor($dif/86400);
}

function DMYtoArray($date)
{
	$dateTime = strToDateTime($date);
    $date_time_array = getdate($dateTime);
    $hours = addZero($date_time_array['hours'],2);
    $minutes = addZero($date_time_array['minutes'],2);
    $month = addZero($date_time_array['mon'],2);
    $day = addZero($date_time_array['mday'],2);
    $year = $date_time_array['year'];
	
	return array('Year'=>substr($year, 2), 'Month'=>$month, 'Day'=>$day, 'Hour'=>$hours, 'Min'=>$minutes);
}

function getYear($date)
{
	$dateTime = strToDateTime($date);
    $date_time_array = getdate($dateTime);
	return $date_time_array['year'];
}

// возвращает строковую дату первый день текущего месяца
function firstDay()
{
	$date = explode(".", date("d.m.Y"));	
	return '01.'.$date[1].'.'.$date[2];
}

// возвращает строковую дату последный день текущего месяца
function lastDay()
{
	$date = explode(".", date("d.m.Y"));	
	$nextMonth = dateAdd('m', 1, '01.'.$date[1].'.'.$date[2]);
	$lastDay = dateAdd('d', -1, date("d.m.Y",$nextMonth));
	
	return date("d.m.Y", $lastDay);
}
?>