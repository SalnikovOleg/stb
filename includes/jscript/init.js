$(document).ready(function() {
	
	if ($(window).width() > 768) {
	
		$(document).mouseout(function(e) {
			if(!cchecker.checkSubmitted() && e.pageY - $(document).scrollTop() <= 5)
			{
				$('#RuBizModal').modal({
					onOpen: modalOpen,
					onClose: simplemodal_close,
					opacity: 80,
					autoResize: true,
					minWidth: 300,
					minHeight: 200,
				});
				cchecker.trackSubmit();
			}
		});

	}	
	
});


function modalOpen (dialog) {
	hideFlash();
	dialog.overlay.fadeIn('fast', function () {
		dialog.container.fadeIn('fast', function () {
			dialog.data.hide().slideDown('fast');
		});
	});
}
function simplemodal_close (dialog) {
	dialog.data.fadeOut('fast', function () {
		dialog.container.hide('fast', function () {
			dialog.overlay.slideUp('fast', function () {
				$.modal.close();
				showFlash();
			});
		});
	});
}
