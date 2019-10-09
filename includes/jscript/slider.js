var sliders = [];

$(document).ready(function(){

	$('#main-slider .slider_bar a[data-index]').click(function(){ 
		var id = $(this).attr('data-id');
		var key = $(this).attr('data-index');
		clearInterval(sliders[id].timer); 
		sliders[id].imgIndex = parseInt(key); 
		load(this); 
		sliders[id].timer = window.setInterval(function(){ next(id); }, sliders[id].interval); 
	});
	for (k in sliders){
		$('#main-slider .slider_bar a[data-id='+k+'][data-index=0]').trigger('click');
		//sliders[k].timer = window.setInterval(function(){ next(k); }, sliders[k].interval);
	}
	
	$('#main-slider .prev').click(function(){
		var id = $(this).attr('data-id');
		next(id);
		
		$(this).attr('data-key', sliders[id].imgIndex)  
	});
	
	$('#main-slider .next').click(function(){
		var id = $(this).attr('data-id');
		prev(id);	
		$(this).attr('data-key', sliders[id].imgIndex) 
	});
	
});

function load(sender){
	var id = parseInt($(sender).attr('data-id'));
	var key = parseInt($(sender).attr('data-index'));
	$('#main-slider .slider_bar a[data-id='+id+']').removeClass('active').addClass('inactive');
	$(sender).removeClass('inactive').addClass('active');
	//$('.slider_img_container[data-id='+id+']').css({"background":"url("+sliders[id].images[key]+") no-repeat right top", "display":"none"});
	$('#main-slider .slider_img_container[data-id='+id+']').attr('src',sliders[id].images[key]);
	$('#main-slider .slider_img_container[data-id='+id+']').fadeIn("slow");
	
	if (document.getElementById('slider_text-'+id))
		$('#main-slider #slider_text-'+id).html(getText(id, key));
	
	$('#main-slider .slider_img_container[data-id='+id+']').html('');
	
	if (typeof sliders[id].href != 'undefined' && typeof sliders[id].href[key] != 'undefined'){
		//$('.slider_img_container[data-id='+id+']').html('<a href="' + sliders[id].href[key] + '"></a>');
		$('.img_wrap[data-id='+id+'] > a').attr('href', sliders[id].href[key]);
	}
	else {$('.img_wrap[data-id='+id+'] > a').attr('href', '#');}
 
}

function getText(id, key){
   if (typeof (sliders[id].texts[key]) == 'undefined' || sliders[id].texts[key] == null) return '';
	var content = '<ul>';
	for (i=0; i < sliders[id].texts[key].length; i++)
		content +='<li>'+sliders[id].texts[key][i];
	content += '</ul>';
	return content;
}

function getHref(id, key){
	return '<a href="' + sliders[id].texts[key] + '"></a>';
}

function next(id){
	sliders[id].imgIndex++;
	if (sliders[id].imgIndex >= sliders[id].images.length)
		sliders[id].imgIndex = 0;
		
	load($('a[data-id='+id+'][data-index='+sliders[id].imgIndex+']'));
}

function prev(id){
	sliders[id].imgIndex--;
	if (sliders[id].imgIndex < 0)
		sliders[id].imgIndex = sliders[id].images.length;
		
	load($('a[data-id='+id+'][data-index='+sliders[id].imgIndex+']'));
}
