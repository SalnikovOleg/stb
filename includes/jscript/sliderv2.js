var sliders = [];

$(document).ready(function(){
	$('.slider_bar a[data-index]').click(function(){ 
		var id = $(this).attr('data-id');
		var key = $(this).attr('data-index');
		clearInterval(sliders[id].timer); 
		sliders[id].imgIndex = parseInt(key); 
		load(this); 
		sliders[id].timer = window.setInterval(function(){ next(id); }, sliders[id].interval); 
	});
	
        for (k in sliders){
	    $('.slider_bar a[data-id='+k+'][data-index=0]').trigger('click');
           //sliders[k].timer = window.setInterval(function(){ next(k); }, sliders[k].interval);
	}
	
});

function load(sender){
	var id = parseInt($(sender).attr('data-id'));
	var key = parseInt($(sender).attr('data-index'));
	$('.slider_bar a[data-id='+id+']').removeClass('active').addClass('inactive');
	$(sender).removeClass('inactive').addClass('active');
	$('.slider_img_container[data-id='+id+']').css({"background":"url("+sliders[id].images[key]+") no-repeat right top", "display":"none"});
	$('.slider_img_container[data-id='+id+']').fadeIn("slow");
	if (document.getElementById('slider_text-'+id))
		$('#slider_text-'+id).html(getText(id, key));
	else 
		$('.slider_img_container[data-id='+id+']').html(getHref(id, key));
 
}

function getText(id, key){
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