var errorValidate = {
    ask_name: "<span id='ask_name_error' style='color:red'>Некорректное имя</span>",
    ask_phone: "<span id='ask_phone_error' style='color:red'>Проверьте правильность номера телефона</span>",
    ask_email: "<span id='ask_email_error' style='color:red'>Проверьте правильность email</span>",
    ask_question: "<span id='ask_question_error' style='color:red'>Введите ваш вопрос</span>",
};
$(document).ready(function () {
    $('#question_button').click(function (e) {
        // alert();
            $('#ask_window').show(300);
            $('#question_button').hide(300);
    });

    $('#ask_close').click(function () {
        $('#ask_window').hide(300);
        $('#question_button').show(300);
    });

    $('#ask_send').click(function () {
        var askData = {};
        var hasError = false;
        //console.log($('#ask_name').val())
        if($('#ask_name').val().match('^[a-zA-Z а-яА-Я]+$') && $('#ask_name').val().length >= 3 && $('#ask_name').val != ''){
            askData.name = $('#ask_name').val();
            if($("#ask_name_error").length > 0){
                $("#ask_name_error").hide();
                hasError = false;
            }
        }else{
            if($("#ask_name_error").length <= 0){
                $(errorValidate.ask_name).insertAfter($("#ask_name"));
            }
            hasError = true;

        }

        if(/^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/.test($('#ask_email').val())){
            askData.email = $('#ask_email').val();
            if($("#ask_email_error").length > 0){
                $("#ask_email_error").hide();
                hasError = false;
            }
        }else{
            if($("#ask_email_error").length <= 0){
                $(errorValidate.ask_email).insertAfter($("#ask_email"));
            }
            hasError = true;

        }

        const regex = /^(\s*)?(\+)?([- _():=+]?\d[- _():=+]?){10,14}(\s*)?$/;
        if ($('#ask_phone').val().match(regex)) {
            askData.phone = $('#ask_phone').val();
            if($("#ask_phone_error").length > 0){
                $("#ask_phone_error").hide();
                hasError = false;
            }
        }else{
            if($("#ask_phone_error").length <= 0){
                $(errorValidate.ask_phone).insertAfter($("#ask_phone"));
            }
            hasError = true;

        }

        if($('#ask_question').val() != ''){
            askData.question = $('#ask_question').val();
            if($("#ask_question_error").length > 0){
                $("#ask_question_error").hide();
                hasError = false;
            }
        }else{
            if($("#ask_question_error").length <= 0){
                $(errorValidate.ask_question).insertAfter($("#ask_question"));

            }
            hasError = true;

        }

        if(!hasError){
            sendQuestion(askData);
        }
    });
});

function sendQuestion(data){
    $.post('ajax.php?function=ask_question', data, function(responce){
        if (responce == 'ok') {
		
            alert("Ваш вопрос успешно отправлен");

			ga('send', 'pageview', '/virtual/ask_question'); //цель для  Universal Analytics
			fbq('track', 'LeaQuestion'); //Лид Файсбукпиксель
            if(window.location.href.search("utm_source=side_button") === -1){
                window.location.href = window.location.href+"?utm_source=side_button";
            }else{
                window.location.href = window.location.href;
            }

        }
    });
}



