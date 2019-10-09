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
	
	$('.order_now').click(function(){
		var form = $(this).closest('div#call_form').get(0);
		if ( checkCallForm(form) == true ){
			var data = { fio : $('#call_fio',form).val(),
						phone : $('#call_phone',form).val(),
						email : $('#call_email',form).val(),
						message  : $('#call_text',form).val(),
						action: 'call'
					};
			$.post('ajax.php?module=contacts', data, function(responce){
					if (responce == 'ok')
						document.location.href='/ru/contacts/call/successfull/';
			});		
		}
	});
	
	$('#call_form .text').click(function(){
		if ( field_list.indexOf($(this).val()) != -1)
			$(this).val('');
	});

	if ( $('#text_block') ) {
		var h = $('#home_text #text_block').height();
		$('#home_text #text_block').height(432);
	
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
});

function navigator_form_submit(pageno) {
	 $.get('/ajax.php?function=set_session&key=pageno&value='+pageno, function(){
	      document.location.reload();
	    });
}

var field_list = ['', 'Ф.И.О.', 'E-MAIL', 'ТЕЛЕФОН'];

function checkCallForm(form){
	//if ((/^(\(\d{3}\))*(\s)*\d{3}(-)*\d{2}(-)*\d{2}$/).test($('#call_phone').val()) == false){
	if ($('#call_phone', form).val().length < 7)	{
		alert('Введите верный телефон');
		return false;
	}
	if ($('#call_fio', form).val().length < 4 || $('#call_fio', form).val() == 'Ф.И.О.') {
		alert('Введите Ваше имя. Больше 3 символов!');
		return false;
	}
	if ( $('#call_email', form).val().length < 5 || $('#call_email', form).val().indexOf('@') == -1 || $('#call_email', form).val() == 'E-MAIL'  ){
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
