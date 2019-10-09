<?php
function prepare_image($filename_src, $filename_dst, $filename_watermark, $w, $h)
{
  // Открываем исходное изображение
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
  // Получаем ширину и высоту исходного изображения
  $src_w = imagesx($imgsource);
  $src_h = imagesy($imgsource);

  // Высчитываем коэффициенты отношения исходных размеров к заданным
  $dx = $src_w / $w;
  $dy = $src_h / $h;

  // Выбираем наибольший коэффициент
  $d = max($dx, $dy);

  // Получаем высоту и ширину результирующего изображения
  $new_w = $src_w / $d;
  $new_h = $src_h / $d;

  // Создаем новое изображение, которое и будет нашим результатом
  $imgdest = imagecreatetruecolor($new_w, $new_h);

  // Копируем изображение-источник в изображение-результат уменьшая его
  imagecopyresampled($imgdest, $imgsource, 0, 0, 0, 0, $new_w, $new_h, $src_w, $src_h);

  // Накладываем ватермарк или логотип
  // Считаем, что картинка всегда PNG, иначе, нужно добавить проверку по расширению
  $imgadd = imagecreatefrompng($filename_watermark);
  imagecopy($imgdest, $imgadd, 0, $new_h-imagesy($imgadd), 0, 0, imagesx($imgadd), imagesy($imgadd));

  // Сохраняем результат
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

  // Чистим мусор
  imagedestroy($imgadd);
  imagedestroy($imgdest);
  imagedestroy($imgsource);
}
else  //если конвертация невозможна то копируем оригинал
{
    copy($filename_src, $filename_dst);
}

}

function resize_image($filename_src, $filename_dst, $w, $h)
{
  // Открываем исходное изображение
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
  // Получаем ширину и высоту исходного изображения
  $src_w = imagesx($imgsource);
  $src_h = imagesy($imgsource);

  // Высчитываем коэффициенты отношения исходных размеров к заданным
  $dx = $src_w / $w;
  $dy = $src_h / $h;

  // Выбираем наибольший коэффициент
  $d = max($dx, $dy);

  // Получаем высоту и ширину результирующего изображения
  $new_w = $src_w / $d;
  $new_h = $src_h / $d;

  // Создаем новое изображение, которое и будет нашим результатом
  $imgdest = imagecreatetruecolor($new_w, $new_h);

  // Копируем изображение-источник в изображение-результат уменьшая его
  imagecopyresampled($imgdest, $imgsource, 0, 0, 0, 0, $new_w, $new_h, $src_w, $src_h);

   // Сохраняем результат
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

  // Чистим мусор
  imagedestroy($imgdest);
  imagedestroy($imgsource);
}
else  //если конвертация невозможна то копируем оригинал
{
    copy($filename_src, $filename_dst);
}

}
?>