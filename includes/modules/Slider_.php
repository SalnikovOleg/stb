<?php
class Slider extends Module{
	private $file = ""; 
	private $images = "";
	private $texts = "";
	private $interval = 0;
	
	function __construct(&$db, &$params)
	{
		parent::__construct($db, $params);
		$this->file = 'conf/'.$_SESSION['lang_folder'].'/slider.cfg';
	}
	
	public function LoadBoxContent()
	{
		$this->loadData();
		$this->tpl->assign_by_ref('list', $this->images);

		$start_index = rand(0, count($this->images)-1);

		$this->tpl->assign('image', $this->images[$start_index]);
		$this->tpl->assign('start_index', $start_index);
		$this->tpl->assign_by_ref('text', $this->texts[$start_index]);
		if ($this->interval == 0) $this->interval = 4000;
		$this->tpl->assign('interval', $this->interval);

		$this->tpl->assign('json_images', json_encode($this->images));
		$this->tpl->assign('json_texts', json_encode($this->texts));
		
		return $this;
	}

	private function loadData()
	{
		$f = file($this->file);

		$img_index = -1;
		$text_index = -1;
		$st_img = false;
		$interval = false;
		
		for ($i=0; $i<count($f); $i++){
	
			$str = trim($f[$i]);
			
			if ( $str== '' || $str[0] == ';') 
				continue;
			
			if ($str == '[images]'){
				$st_img = true;
				continue;
			}	

			if ( strpos($str, 'text-') !== false ){
				$text_index = substr( $str, strpos($str, '-')+1, strpos($str, ']') - strpos($str, '-')-1 );
				$st_img = false;
				continue;
			}	

			if ($str == '[interval]'){
				$text_index = -1;
				$interval = true;
				continue;
			}
			
			if ($st_img){
				$this->images[++$img_index] = $str;
			}	
			
			if ($text_index > -1){
				$this->texts[$text_index][] = $str;
			}	
			
			if ($interval == true){
				$this->interval = (int)$str * 1000;
				$interval = false;
				continue;
			}
		}
	}
}
?>