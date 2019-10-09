function viewPlayer(sender, videoId)
{
	var left = $('#'+sender.id).offset().left - 200;
	var top = $('#'+sender.id).offset().top;
	$('#editForm').css({'display':'block', 'width':500, 'height':400, 'left':left, 'top': top});	
	$('#played_title').html(sender.title);

       $('#videoDiv').html( player.replace(/VIDEOID/g, videoId) );
}

function viewAdminPlayer(sender, videoId)
{
	var left = $('#'+sender.id).offset().left + 60;
	var top = $('#'+sender.id).offset().top;
	$('#editForm').css({'display':'block', 'height':325, 'width':400, 'left':left, 'top': top});	
	$('#played_title').html(sender.title);
        
        $('#videoDiv').html( player.replace(/VIDEOID/g, videoId) );
}

function closevideo(formid)
{
   $('#'+formid).hide();
   $('#videoDiv').html('');
}


//var player = "<object id=\"ytPlayer\" width=\"500\" height=\"380\" type=\"application/x-shockwave-flash\" data=\"http://www.youtube.com/v/VIDEOID?version=3&hl=ru_RU\"><param name=\"allowScriptAccess\" value=\"always\"><param ame=\"allowFullScreen\" value=\"true\"><embed width=\"500\" height=\"300\" allowfullscreen=\"true\" allowscriptaccess=\"always\" type=\"application/x-shockwave-flash\" src=\"http://www.youtube.com/v/VIDEOID?version=3&hl=ru_RU\"></object>";
var player = "<iframe width=\"100%\" height=\"360\" src=\"https://www.youtube.com/embed/VIDEOID?feature=player_embedded\" frameborder=\"0\" allowfullscreen></iframe>";
