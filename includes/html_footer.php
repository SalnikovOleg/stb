<?php
 $db->db_close();
?>

<!-- Start SiteHeart code -->
<!--    <script>-->
<!--    (function(){-->
<!--    var widget_id = 888111;-->
<!--    _shcp =[{widget_id : widget_id}];-->
<!--    var lang =(navigator.language || navigator.systemLanguage -->
<!--    || navigator.userLanguage ||"en")-->
<!--    .substr(0,2).toLowerCase();-->
<!--    var url ="widget.siteheart.com/widget/sh/"+ widget_id +"/"+ lang +"/widget.js";-->
<!--    var hcc = document.createElement("script");-->
<!--    hcc.type ="text/javascript";-->
<!--    hcc.async =true;-->
<!--    hcc.src =("https:"== document.location.protocol ?"https":"http")-->
<!--    +"://"+ url;-->
<!--    var s = document.getElementsByTagName("script")[0];-->
<!--    s.parentNode.insertBefore(hcc, s.nextSibling);-->
<!--    })();-->
<!--    </script>-->
<!-- End SiteHeart code -->
<div id="question_button">
    Задайте вопрос
</div>
<!-- RuBizModal -->
<div id="RuBizModal">
	<div id="close">
		<a href="#" class="modalCloseImg simplemodal-close"><div id="rbm-close" title="Закрыть"></div></a>
	</div>
    <div class="RuBizModal_main">
        <div class="box-modal">
			<div class="block_text">Хотите первыми узнать об акциях и скидках -<br />подписывайтесь на рассылку:</div>
			<form id="subscribe" method="post" action="">
				<input type="text" name="email" value="укажите Ваш email" class="inactive" id="email_subscribe" />
				<input type="button" id="send_subscribe" name="send_subscribe" value="Подписаться" />
			</form>
			<div class="block-social">
				<div class="text">и вступайте в наши <br />группы в социальных сетях:</div>
				<div class="btn">
					<!--a href="http://vk.com/club20131355"><img src="/images/btn-vk.gif" alt="мы Вконтакте" title="мы Вконтакте" /></a-->
					<a href="https://www.instagram.com/study_bridge/"><img src="/images/btn-inst.png" alt="Мы в Instagram" title="Мы в Instagram" /></a>
					<a href="https://www.facebook.com/StudyBridge"><img src="/images/btn-fb.gif" alt="Мы в Фейсбуке" title="Мы в Фейсбуке" /></a>
				</div>
			</div>
		</div>
	</div>
</div>

<div id="ask_window">
    <div class="ask_content">
        <div class="ask_header">
Задайте вопрос
            <span id="ask_close">x</span>
        </div>
        <div class="ask_fields">
            <input type="text" id="ask_name" name="ask_name" placeholder="Представьтесь">
            <input type="text" id="ask_email" name="ask_email" placeholder="Ваш email">
            <input type="text" id="ask_phone" name="ask_phone" placeholder="Ваш телефон">
            <textarea id="ask_question" name="ask_question" cols="30" rows="10" placeholder="Ваш вопрос"></textarea>
            <button id="ask_send" type="button">Отправить вопрос</button>
        </div>
    </div>
</div>
<!-- / RuBizModal -->

</body>
</html>