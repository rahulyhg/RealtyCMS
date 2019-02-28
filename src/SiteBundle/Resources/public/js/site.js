/**
 * Created by NNovi on 19.03.2016.
 */
$(document).on("change", ".changeable", function () {
    var form = $(this).closest('form');
    var form_data = form.serialize();
    form.find('input').attr('disabled','disabled');
    form.find('select').attr('disabled','disabled');
    $.ajax({
        url  : form.data('update'),
        type : "GET",
        data : form_data,
        success : function (data) {
            $('.search_objects').html(data);
            $('.selectpicker').selectpicker();
            form_number();
        }
    });
});
$(document).ready(function() {
    $('.phone').inputmask('+9(999)999-9999');
    $(":input").inputmask();
    $('.from').keyup(function(){
      $(this).val($(this).val().toString().replace(/\s/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, " "));
    });
    $('.to').keyup(function(){
      $(this).val($(this).val().toString().replace(/\s/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, " "));
    });
    $('.selectpicker').selectpicker();
    form_number();	
	var menu_pos = $(".menu").offset().top+50;
	$(window).scroll(function () {		
		if ($(this).scrollTop() >= menu_pos) {
			$(".menu").addClass("fixed");
		} else {
			$(".menu").removeClass("fixed");
		}
	});
	$('.antibtn').antibtn();
    if ($(window).width() > 990 && $(document).height() > 2000 && $('.search_objects').height() < ($(window).height()-200)) {
        (function ($) {
            $.lockfixed(".search_objects", { offset: { top: 80, bottom: 450 } });
        })(jQuery);
    }
});
function form_revision(){
  $('.from').each(function(){    
    $(this).val($(this).val().toString().replace(/\s/g, ''));    
  });
  $('.to').each(function(){    
    $(this).val($(this).val().toString().replace(/\s/g, ''));    
  });
}
function form_number(){
  $('.from').each(function(){    
    $(this).val($(this).val().toString().replace(/\B(?=(\d{3})+(?!\d))/g, " "));    
  });
  $('.to').each(function(){    
    $(this).val($(this).val().toString().replace(/\B(?=(\d{3})+(?!\d))/g, " "));    
  });
}
$(document).on("submit", ".feedback_form", function (e) {
    console.log('Submitting feedback form');
	e.preventDefault();
    var error = '';
    var form = $(this);
    var inputs = form.find('input');
    $.each( inputs, function( key, value ) {
      if (!$(value).val()) {        
        $(value).closest('.form-group').addClass('has-error');
        error = 'yes';
      } else {
        $(value).closest('.form-group').removeClass('has-error');
        $(value).closest('.form-group').addClass('has-success');
      }
    });
    if (!error) {
      $.ajax({
        url  : "/post",
        type : "POST",
        data : form.serialize(),
        success : function (data) {
            $('.fb_reload').html(data);
            $(this).find('.btn').hide();
        }
      });
    }
});

$(document).on('click', '[data-toggle="modal"]', function () {
    var target = $($(this).attr('data-target') || (href && href.replace(/.*(?=#[^\s]+$)/, '')));
    if ($(this).attr('data-object')) {
        var id = $(this).attr('data-object');
        if ($('.hid_field').val()) {
            $('.hid_field').val(id);
        } else {
            $(target).find('form').append('<input type="hidden" class="hid_field" name="object" value="' + id + '">');
        }
    }
    if ($(this).attr('data-agent')) {
        var id = $(this).data('agent');
        var photo = $(this).find('img').data('src');        
        var name = $(this).find('img').attr('alt');
        if ($('.agent_photo_m').attr('src')) {
            $('.agent_photo_m').attr('src', photo);
            $('.agent_photo_m').attr('alt', name);
        } else {
            $(target).find('.photo').append('<img class="agent_photo_m img-circle" width="200" src="'+photo+'" alt="'+name+'">');
        }
        if ($('.agent_name_m').data('id')) {
            $('.agent_name_m').html(name);
        } else {
            $(target).find('.photo').append('<h4 class="agent_name_m" data-id="ff">'+name+'</h4>');
        }        
        if ($('.hid_field').val()) {
            $('.hid_field').val(id);
        } else {
            $(target).find('form').append('<input type="hidden" class="hid_field" name="agent" value="'+id+'">');
        }
    }
});
(function($){
    $(document).ready(function(){
        $('ul.dropdown-menu [data-toggle=dropdown]').on('click', function(event) {
            event.preventDefault();
            event.stopPropagation();
            $(this).parent().siblings().removeClass('open');
            $(this).parent().toggleClass('open');
        });
    });
})(jQuery);