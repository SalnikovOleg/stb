<?php
class Slider extends Module{
	private $file = ""; 
	private $images = "";
	private $texts = "";
	private $href = "";
	private $interval = 0;
	private $id = 0; 
	
	function __construct(&$db, &$params)
	{
		$this->id = $params['id']; 
		parent::__construct($db, $params);

	}

// чтение параметров
	private function getParams()
	{
		if ($this->Params['params'] != '')
			$this->Params = json_decode($this->Params['params'], true);

		if (isset($this->Params['config']))
			$this->file = 'conf/'.$_SESSION['lang_folder'].'/'.$this->Params['config'];
		else
			$this->file = 'conf/'.$_SESSION['lang_folder'].'/slider.cfg';
	
		if (isset($this->Params['template'])) 
			$this->tplName = $this->Params['template'];
		else
			$this->tplName = 'slider.html';
		
		if (isset($this->Params['css'])) 	
			$this->CSSFile = $this->Params['css'];
		else	
			$this->CSSFile = 'slider.css';
	}

// создание пустых файлов конфиг, шаблон и css
	private function createFiles()
	{
		if (!file_exists($this->file)) write_to_file($this->file, "");	
		if (!file_exists('templates/'.CURRENT_TEMPLATE.'box/'.$this->tplName)) write_to_file('templates/'.CURRENT_TEMPLATE.'box/'.$this->tplName, $this->defaultSliderTemplate());
		if (!file_exists('templates/'.CURRENT_TEMPLATE.'box/'.$this->tplName)) write_to_file('templates/'.CURRENT_TEMPLATE.'box/'.$this->tplName, $this->defaultSliderTemplate());
		if (!file_exists('templates/'.CURRENT_TEMPLATE.'css/'.$this->CSSFile)) write_to_file('templates/'.CURRENT_TEMPLATE.'css/'.$this->CSSFile, $this->defaultSliderCss());
	}
	
	public function LoadBoxContent()
	{
		$this->getParams();
		$this->createFiles();
		
		$this->loadData();
		$this->tpl->assign_by_ref('list', $this->images);

		$start_index = rand(0, count($this->images)-1);
		
		if ( isset($this->images[$start_index]) )
			$this->tpl->assign('image', $this->images[$start_index]);
		$this->tpl->assign('start_index', $start_index);
		$this->tpl->assign_by_ref('text', $this->texts[$start_index]);
		$this->tpl->assign_by_ref('href', $this->href[$start_index][0]);
		
		if ($this->interval == 0) $this->interval = 4000;

		$this->tpl->assign('interval', $this->interval);
		$this->tpl->assign('json_images', json_encode($this->images));
		$this->tpl->assign('json_texts', json_encode($this->texts));
		$this->tpl->assign('json_href', json_encode($this->href));

		$this->tpl->assign('id', $this->id);
		
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

			if ( strpos($str, '[text-') !== false ){
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
				if ( strpos($str, 'http://') !== false )
					$this->href[$text_index][] = $str;
				else
					$this->texts[$text_index][] = $str;
			}	
			
			if ($interval == true){
				$this->interval = (int)$str * 1000;
				$interval = false;
				continue;
			}
		}
	}
	
	private function defaultSliderTemplate()
	{
	return '<div id="slider_container">
<div id="slider_img_container" style="background:url({$image}) no-repeat right top"><a href="{$text[0]}"></a></div>
<div id="slider_bar">
{foreach from=$list key=key item=item}<a href="javascript:void(0);" class="{if $key == $start_index}active{else}inactive{/if}" id="{$key}"></a>{/foreach}
</div>
</div>

<script type="text/javascript">
var interval = {$interval};
var imgIndex = {$start_index};
var images = {$json_images};
var texts =  {$json_texts};
</script>';
	}
	
	private function defaultSliderCss()
	{
	return '#slider_container{
	float:left;
	width:290px;
	height:107px;
}

#slider_img_container{
	float:left;
	width:270px;
	height:107px;
}

#slider_img_container a {	
	width:270px;
	height:107px;
	display:block;
}
#slider_bar{
	float:left;
	width:15px;
	padding-left:5px;
}

#slider_bar a{
	display:block;
	height:11px;
	width:11px;
	margin-bottom:5px;
}

#slider_bar a.inactive{
	background:url(../images/slider_button2.gif) no-repeat center;
}

#slider_bar a.active{
	background:url(../images/slider_button_active2.gif) no-repeat center;
}';	
	}
}
?>
