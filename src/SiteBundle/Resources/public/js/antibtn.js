// Antibot submit button
// By Nikita Novichkov

(function($) {
	
	// plugin definition
	$.fn.antibtn = function(options) {
		
		// extend default settings with those provided.
		var settings = $.extend(
			{},
			$.fn.antibtn.defaults,
			options
		);
		
		// iterate each matched element
		return this.each(function() {
			
			// cache clicked button object
			var $btn = $(this),
			btn_text = $btn.text(),
			btn_width = $btn.width(),
			btn_textColor = $btn.css("color"),
			btn_borderColor = $btn.css("borderColor"),
			btn_backgroundColor = $btn.css("backgroundColor"),
			btn_onclick_event = $btn.attr("onclick");
			$btn.attr("onclick","");
			var timer = null;
			$btn.mousedown(function(e) {
				if(!/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {
					e.preventDefault();
					e.stopPropagation();
					$btn.css("min-width", btn_width);
					$btn.css("borderColor", settings.borderColor);
					$btn.css("backgroundColor", settings.backgroundColor);
					$btn.css("color", settings.color);
					$btn.text(settings.waitTextBefore);
					var time = Math.round(settings.timer / 1000);
					$btn.text(settings.waitTextBefore + ' ' + time + ' ' + settings.waitTextAfter);
					time--;
					var timer = setInterval(function () {
						if (time <= 0) {
							clearInterval(timer);
							$btn.css("borderColor", btn_borderColor);
							$btn.css("backgroundColor", btn_backgroundColor);
							$btn.css("color", btn_textColor);
							$btn.text(btn_text);
							time = Math.round(settings.timer / 1000);
							if (btn_onclick_event === undefined) {
								console.log('submit');
								$($btn.closest('form')).submit();
							} else {
								$btn.attr("onclick", btn_onclick_event);
							}
						} else {
							$btn.text(settings.waitTextBefore + ' ' + time + ' ' + settings.waitTextAfter);
							time--;
						}
					}, 1000);
					$btn.mouseup(function () {
						clearInterval(timer);
						$btn.css("borderColor", btn_borderColor);
						$btn.css("backgroundColor", btn_backgroundColor);
						$btn.css("color", btn_textColor);
						$btn.text(btn_text);
						time = Math.round(settings.timer / 1000);
					});
				}
			});
			if(!/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {
				$btn.click(function (e) {
					e.preventDefault();
					e.stopPropagation();
				});
			}
		});
	};
	
	// plugin default settings
	$.fn.antibtn.defaults = {
		timer: 3000, //milliseconds
		waitTextBefore: 'Ждите',
		waitTextAfter: 'сек.',
		color: 'green',
		borderColor: 'green',
		backgroundColor: 'white'
	};

// end closure 
})(jQuery);