
$(document).ready(function(){
	$('.product_view').click(function(){
	    $.get('/ajax.php?function=set_session&key=view&value='+$(this).val(), function(){
	      document.location.reload();
	    });
	});
	
	$('.item_on_page').click(function(){
	    $.get('/ajax.php?function=set_session&key=on_page&value='+$(this).val(), function(){
	      document.location.reload();
	    });
	});
	
	/* подписка */
	$('#send_subscribe').click(function(){
		
		var value = $('#email_subscribe').val();
		
		if (validate_email(value) == false){
			alert('введите email');
			return;
		}
		
		var url = 'ajax.php?module=Subscribe&method=subscribe&email='+value;
		$.get(url, function(){
			alert('Подписка успешно выполнена. Спасибо!');
		});
		
		$('#email_subscribe').val('Введите Ваш email');
	});
	
	$('#email_subscribe').click(function(){ $(this).val(''); });
	
	// отправка заявки
	$('#sendform').click(function(){
		send_order();
	});
	
	$('.short_text').css({'height':'100px', 'overflow':'hidden'});
	
	$('.toggle_text').click(function(){
		if( $('.short_text').height() == 100 ){
			$('.short_text').css({'height':'100%'});
			$(this).html('Свернуть'); 
		}
		else{ 
			 $('.short_text').css('height','100px'); 
			 $(this).html('Читать далее ...');
		 }
	});
	
	// отправка заявок
	$('.order_now').click(function(){
		var form = $(this).closest('div.reg_form').get(0);
		if ( checkCallForm(form) == true ){
			var action = $('input[name=action]',form).get(0) ? $('input[name=action]',form).val() : 'call';
			action =  (action == undefined )? 'call' : action; 
			var data = {
						id : $('input[name=id]', form) ? $('input[name=id]', form).val() : 0,
                		id_page : $('input[name=id_page]', form) ? $('input[name=id_page]', form).val() : 0,
						fio : $('input[name=call_fio]',form).val(),
						phone : $('input[name=call_phone]',form).val(),
						email : $('input[name=call_email]',form).val(),
						message  : $('#call_text',form) ? $('#call_text',form).val() : '',
						action:  action,
						url:document.location.href
					};
			if (action == 'call') {	
			      $.post('ajax.php?module=contacts', data, function(responce){
					if (responce == 'ok')
						alert('Спасибо за интерес! Наши консультанты свяжутся с Вами в ближайшее время.');
						ga('send', 'pageview', '/virtual/consult'); //цель для  Universal Analytics
						fbq('track', 'LeaConsult'); //Лид Файсбукпиксель
						//_gaq.push(['_trackPageview','/virtual/consult']);
			      });		
			} else if (action == 'webinar') {
			      $.post('ajax.php?function=send_registration', data, function(responce){
    					if (responce == 'ok') {
    					    $('.order_now', form).html('Отправлено').removeClass('order_now').addClass('successfully');
    					}
			      });		
			} 
		}
	});
	
	$('#call_form .text').click(function(){
		if ( field_list.indexOf($(this).val()) != -1)
			$(this).val('');
	});

	var main_block_text_height;
	
	if ($(window).width() <= 767) {
		main_block_text_height = 232;
	}
	else {
		main_block_text_height = 432;
	}
	
	if ( $('#text_block') ) {
		var h = $('#home_text #text_block').height();
		$('#home_text #text_block').height(main_block_text_height);
	
		$('#home_text .more_button').click(function(){
			$(this).hide();
			$('#home_text #text_block').height(h);
		});	
	}
	
	if( $('.order_button')) {
		$('.order_button, .order_now1').click(function(){ $('#callback').trigger('click'); });
	}

	$("ul.tabs li").click(function(){
		var c = $(this).attr('class');
		if (c.indexOf("current") >= 0) return;
		$(".box").animate({ opacity: "hide" }, "fast");
		$("div."+c).animate({ opacity: "show" }, "fast");
		$(this).addClass('current').siblings().removeClass('current');
	});
	
	$('#action').closest('li').css('backgroundColor','#EC5E5D');
	
	$('#call_phone, #call_email').on('blur', function(){
		var form = $(this).closest('div#call_form').get(0);
		saveForm($(this), form, 'call_');
	});
	
	$('#phone, #email').on('blur', function(){
		var form = $(this).closest('form').get(0);
		saveForm($(this), form, '');
	});		
});


function navigator_form_submit(pageno) {
	 $.get('/ajax.php?function=set_session&key=pageno&value='+pageno, function(){
	      document.location.reload();
	    });
}

var field_list = ['', 'Ф.И.О.', 'E-MAIL', 'ТЕЛЕФОН'];

function checkCallForm(form){
	//if ((/^(\(\d{3}\))*(\s)*\d{3}(-)*\d{2}(-)*\d{2}$/).test($('#call_phone').val()) == false){
	if ($('input[name=call_fio]', form).val().length < 4 || $('input[name=call_fio]', form).val() == 'Ф.И.О.') {
		alert('Введите Ваше имя. Больше 3 символов!');
		return false;
	}
	if ($('input[name=call_phone]', form).val().length < 7  || $('input[name=call_phone]', form).val() == 'ТЕЛЕФОН')	{
		alert('Введите верный телефон');
		return false;
	}	
	if ( $('input[name=call_email]', form).val().length < 5 || $('input[name=call_email]', form).val().indexOf('@') == -1 || $('input[name=call_email]', form).val() == 'E-MAIL'  ){
		alert('Введите e-mail!');
		return false;
	}
	return true;
}

function openWindow(url, w, h){
	open(url, "reportWindow", "status=no, toolbar=no, menubar=yes, scrollbars=yes, width="+w+", height="+h);
}

// очистка полей формы
function doClear(id){
	var f = document.getElementById(id);
	for(i=0; i<f.elements.length; i++)
		f.elements[i].value = "";
}

function refresh(){
	window.location.href = window.location;
}

function divClose(id){
	$('#'+id).fadeOut('slow');
}

function element(id){
	return document.getElementById(id);
}

// удаление переносов
function clearnl(text){
	return text.replace(/(\n(\r)?)/g, ' ');
}

// функция упаковывает все введенные данные на форме в одну строку
function packFormData(f)
{
	var data = '{';
	var form = document.getElementById(f);
	for(i=0; i<form.elements.length; i++){
		if(form.elements[i].getAttribute('type')=='checkbox' )
		{	if (form.elements[i].checked == true)
				data +='"'+form.elements[i].getAttribute('name')+'" : "on", ';
		}
		else 
		if (form.elements[i].getAttribute('type')=='radio')
		{	if (form.elements[i].checked == true)	
				data +='"'+form.elements[i].getAttribute('name')+'" : "'+form.elements[i].value+'",';
		}		
		else if (form.elements[i].getAttribute('type') == 'text' || form.elements[i].getAttribute('type') == 'hidden')  
			data +='"'+form.elements[i].getAttribute('name')+'" : "'+form.elements[i].value+'",';
		else 
			data +='"'+form.elements[i].getAttribute('name')+'" : "'+clearnl(form.elements[i].value)+'",';
	}

	data +='"system" : "1"}';	
	
	return data;
}


// плагин для поля со значением по умолчанию поиск
(function($) {
	$.fn.inputDefualts = function(options) {
		// дефолтные значения
		var defaults = {
 			cl: 'inactive', // имя класса для неактивного состояния
 			text: this.val()   // значение берется из самого инпута
  		}, 	opts = $.extend(defaults, options);	
  		
  		this.addClass(opts['cl']); 	// добавляем класс к инпуту
  		this.val(opts['text']);			// ставим значение по умолчанию
  		
  		// обрабатываем события фокуса на поле
  		this.focus(function() {
  			if($(this).val() == opts['text']) $(this).val(''); // обнуляем его, если надо
  			$(this).removeClass(opts['cl']); // убираем класс
  		});
  		
  		// теперь очередь блюра
  		this.blur(function() {
  			if($(this).val() == '') {
  				$(this).val(opts['text']); 			// возвращаем значение
  				$(this).addClass(opts['cl']); 	// и класс, если надо
  			}
  		});
	};
	
})(jQuery);


function validate_email(value){
 reg = /^[-\._a-z,A-Z,0-9]+@[-_\.a-z,A-Z,0-9]+\.[a-z,A-Z]+$/;
 if (reg.test(value)) return true;
 else return false;
}

function validate_phone(value){
  reg = /^(\(\d{3}\))*\s*(\d+(\s|-)*)+$/;
 if (reg.test(value)) return true;
 else return false;
}

function validate_name(value){
 if (value.trim().length < 3) return false;
 reg = /[^0-9]+/;
 if (reg.test(value)) return true;
 else return false;
}

function validate_empty(value){
	if (value.trim().length < 10 || value.trim().length >1000) return false;
	else return true;
}

// отображение превью всех изображений
function getAllImages(container, module, id){
	$('#'+container).load('ajax.php?module='+module+'&method=getAllImages&id='+id);
	var w = Math.floor($(window).width()/2) - Math.floor($('#'+container).width()/2);
	var h = Math.floor($(window).height()/2) - Math.floor($('#'+container).height()/2);
	$('#'+container).css({'display':'block','left':w+'px', 'top':h+'px'});
}

function send_order()
{
	var country = [];
	$('input[name=country]').each(function(i){
		if ($(this).is(':checked') )
			country.push($(this).val());
	});
	var program = [];
	$('input[name=program]').each(function(i){
		if ($(this).is(':checked') )
			program.push($(this).val());
	});		
	var data = {
		name : $('#name').val(),
		mail : $('#mail').val(),
		phone : $('#phone').val(),
		age : $('#age').val(),
		school : $('#school').val(),
		country : country,
		program : program
	}
	
	if (validate_email(data.mail) == false){
		alert('введите правильный email');
		return;
	}
	
	if (validate_phone(data.phone) == false){
		alert('введите правильный телефон');
		return;
	}

	$.post('/ajax.php?function=send_meeting_order', data, function(){ alert('Спасибо за интерес! Наши менеджеры свяжутся с Вами в ближайшее время.'); });
}

function saveForm(sender, form, prefix) {
	if ( $(sender).val().trim == '') return;
	if ( $(sender).attr('id') == prefix + 'phone' && !validate_phone($(sender).val()) ) return;
	if ( $(sender).attr('id') == prefix + 'email' && !validate_email($(sender).val()) ) return; 
		
	message_name = 'message';		
	if (prefix == 'call_') 
		message_name = 'call_text';
				
	var data = {
		id :  $('input[name=id]', form).val(),
		fio : $('#'+prefix + 'fio', form).val(),
		phone : $('#'+prefix + 'phone', form).val(),
		email : $('#'+prefix + 'email', form).val(),
		message : $('#'+message_name, form).val(),
		url : document.location.href 
	};
	
	$.post('/ajax.php?module=Message&method=saveform', data, function(responce){
		if (!isNaN(responce))
			$('input[name=id]', form).val(responce);
	});
}
