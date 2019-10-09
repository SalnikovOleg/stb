<?php
class ImgConvert 
{
	private $imageTemplate = "";

	private $newWidth = 0;
	private	$newHeight = 0;
	private $func = "";
	private $source = null;
	private $target = null;
	private $watermark = null;
	private $saveFormat = "jpg";

	public $result = false;
	
	public function __construct($func, $width, $height, $imgTpl = "", $defW = 100, $defH = 100)
	{
		$this->imageTemplate = $imgTpl;
		
		if ($width>0) $this->newWidth = $width;
		else $this->newWidth = $defW;
		
		if($height>0) $this->newHeight = $height;
		else $this->newHeight = $defH;
		
		$this->func = $func;
	}

	// ширина нового изображения
	public function setWidth($value)
	{
		$this->newWidth = $value;
	}
	
	// высота нового изображения
	public function setHeyght($value)
	{
		$this->newHeight($value);
	}
	
	// файл шаблон картинки
	public function setTemplate($value)
	{
		$this->imageTemplate = $value;
	}
	
	public function convert($source_fName)
	{
		$this->saveFormat = strtolower(end(explode(".", $source_fName)));

		$this->source = $this->loadImage($source_fName);

		if ($this->source != null)
		{	
			//выполним заданное преобразование
			$foo = $this->func;
			$this->$foo();
		}
	}
	
	// загрузка  изображения в память
	private function loadImage($fName)
	{
		$ext = strtolower(end(explode(".", $fName)));
		switch ($ext)
		{
			case 'jpg': 
			case 'jpeg':
				$imgsource = imagecreatefromjpeg($fName);
				break;
			case 'gif':	
				$imgsource = imagecreatefromgif($fName);
				break;
			case 'png': 
				$imgsource = imagecreatefrompng($fName);
				break;
			default :$imgsource = null;
		}
		
		return $imgsource;
	}
	
	// Сохраняем результат
	public function saveImage($fName)
	{
		switch ($this->saveFormat)
		{
			case 'jpg': 
			case 'jpeg':
				imagejpeg($this->target, $fName, 100);
				break;
			case 'gif':
				imagegif($this->target, $fName, 100);
				break;
			case 'png':
				imagepng($this->target, $fName, 9);
				break;
		}

		// Чистим мусор
		imagedestroy($this->target);
		imagedestroy($this->source);
	}

	// Получаем высоту и ширину результирующего изображения
	private function getNewSizes()
	{
			// Получаем ширину и высоту исходного изображения
		$src_w = imagesx($this->source);
		$src_h = imagesy($this->source);
		
		// Высчитываем коэффициенты отношения исходных размеров к заданным
		$dx = $src_w / $this->newWidth;
		$dy = $src_h / $this->newHeight;

		// Выбираем наибольший коэффициент
		$d = max($dx, $dy);

		$this->newWidth = round($src_w / $d);
		$this->newHeight = round($src_h / $d);
	}

	
	// Копируем изображение-источник в изображение-результат уменьшая его
	private function resize()
	{
		// рассчет новых значений размеров
		$this->getNewSizes();
		// Создаем новое изображение, которое и будет нашим результатом
		$this->target = imagecreatetruecolor($this->newWidth, $this->newHeight);

		imagecopyresampled($this->target, $this->source, 0, 0, 0, 0, $this->newWidth, $this->newHeight, imagesx($this->source), imagesy($this->source));		
	}
	
	//	Уменьшить и наложить водяной знак
	private function resizeAddThreadMark()
	{
		if ($this->imageTemplate == "") 
		{
			$this->resize();
			return;
		}
		
		// рассчет новых значений размеров
		$this->getNewSizes();
		
		// создание уменьшенного изображения
		$temp = imagecreatetruecolor($this->newWidth, $this->newHeight);
		imagecopyresampled($temp, $this->source, 0, 0, 0, 0, $this->newWidth, $this->newHeight, imagesx($this->source), imagesy($this->source));

		//загрузка водяного знака
		$this->watermark = $this->loadImage($this->imageTemplate);
		$wm_x = imagesx($this->watermark);
		$wm_y = imagesy($this->watermark);
		
		//определим координаты нового изображения с учетом размеров водяного знака и координаты точки размещения водяного знака и изображения
		if ($wm_x > $this->newWidth) 
		{	
			$tx = (int)($wm_x/2) - (int)($this->newWidth/2);
			$wx = 0;
			$this->newWidth = $wm_x;
		}
		else 
		{
			$tx = 0;
			$wx = (int)($this->newWidth/2) - (int)($wm_x/2);
		}
		
		if ($wm_y > $this->newHeight) 
		{
			$ty = (int)($wm_y/2) - (int)($this->newHeight/2);
			$wy = 0;
			$this->newHeight = $wm_y;
		}
		else
		{
			$ty = 0;
			$wy = (int)($this->newHeight/2) - (int)($wm_y/2);
		}
		
		// Создаем новое изображение, которое и будет нашим результатом
		$this->target = imagecreatetruecolor($this->newWidth, $this->newHeight);
		
		//копируем в него уменьшенное изображение
		imagecopy($this->target, $temp, $tx, $ty, 0, 0, imagesx($temp), imagesy($temp));
		
		// копируем водяной знак на изображение
		imagecopy($this->target, $this->watermark, $wx, $wy, 0, 0, imagesx($this->watermark), imagesy($this->watermark));
		
		imagedestroy($this->watermark);
		imagedestroy($temp);
	}
	
	//	Уменьшить и подложить фон
	private function resizeAddBackground()
	{
		if ($this->imageTemplate == "")	
		{
			$this->resize();
			return;
		}
		
		// рассчет новых значений размеров
		$this->getNewSizes();
		
		// создание уменьшенного изображения
		$temp = imagecreatetruecolor($this->newWidth, $this->newHeight);
		imagecopyresampled($temp, $this->source, 0, 0, 0, 0, $this->newWidth, $this->newHeight, imagesx($this->source), imagesy($this->source));

		//загрузка фона
		$this->watermark = $this->loadImage($this->imageTemplate);
		$wm_x = imagesx($this->watermark);
		$wm_y = imagesy($this->watermark);
		
		//определим координаты нового изображения с учетом размеров водяного знака и координаты точки размещения водяного знака и изображения
		if ($wm_x > $this->newWidth) 
		{	
			$tx = (int)($wm_x/2) - (int)($this->newWidth/2);
			$wx = 0;
			$this->newWidth = $wm_x;
		}
		else 
		{
			$tx = 0;
			$wx = (int)($this->newWidth/2) - (int)($wm_x/2);
		}
		
		if ($wm_y > $this->newHeight) 
		{
			$ty = (int)($wm_y/2) - (int)($this->newHeight/2);
			$wy = 0;
			$this->newHeight = $wm_y;
		}
		else
		{
			$ty = 0;
			$wy = (int)($this->newHeight/2) - (int)($wm_y/2);
		}
		
		// Создаем новое изображение, которое и будет нашим результатом
		$this->target = imagecreatetruecolor($this->newWidth, $this->newHeight);
		
		// копируем водяной знак на изображение
		imagecopy($this->target, $this->watermark, $wx, $wy, 0, 0, imagesx($this->watermark), imagesy($this->watermark));
		
		//копируем в него уменьшенное изображение
		imagecopy($this->target, $temp, $tx, $ty, 0, 0, imagesx($temp), imagesy($temp));
	
		imagedestroy($this->watermark);
		imagedestroy($temp);
		$this->saveFormat = strtolower(end(explode(".", $this->imageTemplate)));		
	}
	
	//	Отрезать от оригинала
	private function cut()
	{
		$this->target = imagecreatetruecolor($this->newWidth, $this->newHeight);
		imagecopyresampled($this->target, $this->source, 0, 0, 0, 0, $this->newWidth, $this->newHeight, $this->newWidth, $this->newHeight);
	}
}
?>