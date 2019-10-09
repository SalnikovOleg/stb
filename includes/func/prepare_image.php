<?php
function prepare_image($filename_src, $filename_dst, $filename_watermark, $w, $h)
{
  // ��������� �������� �����������
 $ext = strtolower(end(explode(".", $filename_src)));

$converting = true;
  switch ($ext)
{
  case 'jpg': 
  case 'jpeg':
	$imgsource = imagecreatefromjpeg($filename_src);
        break;
  case 'gif':	
	$imgsource = imagecreatefromgif($filename_src);
        break;
  case 'png': 
        $imgsource = imagecreatefrompng($filename_src);
        break;
  default :$converting = false;
}

if ($converting) 
{
  // �������� ������ � ������ ��������� �����������
  $src_w = imagesx($imgsource);
  $src_h = imagesy($imgsource);

  // ����������� ������������ ��������� �������� �������� � ��������
  $dx = $src_w / $w;
  $dy = $src_h / $h;

  // �������� ���������� �����������
  $d = max($dx, $dy);

  // �������� ������ � ������ ��������������� �����������
  $new_w = $src_w / $d;
  $new_h = $src_h / $d;

  // ������� ����� �����������, ������� � ����� ����� �����������
  $imgdest = imagecreatetruecolor($new_w, $new_h);

  // �������� �����������-�������� � �����������-��������� �������� ���
  imagecopyresampled($imgdest, $imgsource, 0, 0, 0, 0, $new_w, $new_h, $src_w, $src_h);

  // ����������� ��������� ��� �������
  // �������, ��� �������� ������ PNG, �����, ����� �������� �������� �� ����������
  $imgadd = imagecreatefrompng($filename_watermark);
  imagecopy($imgdest, $imgadd, 0, $new_h-imagesy($imgadd), 0, 0, imagesx($imgadd), imagesy($imgadd));

  // ��������� ���������
  switch($ext)
 {
     case 'jpg': 
     case 'jpeg':
	 imagejpeg($imgdest, $filename_dst, 90);
         break;
     case 'gif':
         imagegif($imgdest, $filename_dst, 90);
        break;
     case 'png':
         imagepng($imgdest, $filename_dst, 90);
        break;
//     case 'bmp':
//         imagebmp($imgdest, $filename_dst, 90);
//        break;


 }

  // ������ �����
  imagedestroy($imgadd);
  imagedestroy($imgdest);
  imagedestroy($imgsource);
}
else  //���� ����������� ���������� �� �������� ��������
{
    copy($filename_src, $filename_dst);
}

}

function resize_image($filename_src, $filename_dst, $w, $h)
{
  // ��������� �������� �����������
 $ext = strtolower(end(explode(".", $filename_src)));

$converting = true;
  switch ($ext)
{
  case 'jpg': 
  case 'jpeg':
	$imgsource = imagecreatefromjpeg($filename_src);
        break;
  case 'gif':	
	$imgsource = imagecreatefromgif($filename_src);
        break;
  case 'png': 
        $imgsource = imagecreatefrompng($filename_src);
        break;
  default :$converting = false;
}

if ($converting) 
{
  // �������� ������ � ������ ��������� �����������
  $src_w = imagesx($imgsource);
  $src_h = imagesy($imgsource);

  // ����������� ������������ ��������� �������� �������� � ��������
  $dx = $src_w / $w;
  $dy = $src_h / $h;

  // �������� ���������� �����������
  $d = max($dx, $dy);

  // �������� ������ � ������ ��������������� �����������
  $new_w = $src_w / $d;
  $new_h = $src_h / $d;

  // ������� ����� �����������, ������� � ����� ����� �����������
  $imgdest = imagecreatetruecolor($new_w, $new_h);

  // �������� �����������-�������� � �����������-��������� �������� ���
  imagecopyresampled($imgdest, $imgsource, 0, 0, 0, 0, $new_w, $new_h, $src_w, $src_h);

   // ��������� ���������
  switch($ext)
 {
     case 'jpg': 
     case 'jpeg':
	 imagejpeg($imgdest, $filename_dst, 90);
         break;
     case 'gif':
         imagegif($imgdest, $filename_dst, 90);
        break;
     case 'png':
         imagepng($imgdest, $filename_dst, 90);
        break;
 }

  // ������ �����
  imagedestroy($imgdest);
  imagedestroy($imgsource);
}
else  //���� ����������� ���������� �� �������� ��������
{
    copy($filename_src, $filename_dst);
}

}
?>