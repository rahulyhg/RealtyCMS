/**
 * Created by NNovi on 15.03.2016.
 */
function fill_alias() {
    var value = $("input[id*='name']").val();
    if (!value) {
        var value = $("input[id*='title']").val();
    }
    $("input[id*='alias']").val(translit(value));
}
function capitalize(item) {
    text = $(item).parent().closest('div').children('input').val();
    new_text = text.toLowerCase();
    new_text = (new_text.substr(0,1).toUpperCase())+(new_text.substr(1));
    $(item).parent().closest('div').children('input').val(new_text);
}
function ucwords(str){
    str.toLowerCase();
    return str.replace(/(\b)([a-zA-Z])/g,
        function(firstLetter){
            return   firstLetter.toUpperCase();
        });
}
function translit(value) {
    var rusChars = new Array('а', 'б', 'в', 'г', 'д', 'е', 'ё', 'ж', 'з', 'и', 'й', 'к', 'л', 'м', 'н', 'о', 'п', 'р', 'с', 'т', 'у', 'ф', 'х', 'ч', 'ц', 'ш', 'щ', 'э', 'ю', '\я', 'ы', 'ъ', 'ь', ' ', '\'', '\"', '\#', '\$', '\%', '\&', '\*', '\,', '\:', '\;', '\<', '\>', '\?', '\[', '\]', '\^', '\{', '\}', '\|', '\!', '\@', '\(', '\)', '\-', '\=', '\+', '\/', '\\');
    var transChars = new Array('a', 'b', 'v', 'g', 'd', 'e', 'jo', 'zh', 'z', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'r', 's', 't', 'u', 'f', 'h', 'ch', 'c', 'sh', 'csh', 'e', 'ju', 'ja', 'y', '', '', '_', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '');
    var from = value;
    from = from.toLowerCase();
    var to = "";
    var len = from.length;
    var character,
        isRus;
    for (var i = 0; i < len; i++) {
        character = from.charAt(i, 1);
        isRus = false;
        for (var j = 0; j < rusChars.length; j++) {
            if (character == rusChars[j]) {
                isRus = true;
                break;
            }
        }
        to += (isRus) ? transChars[j] : character;
    }
    return to;
}
$('.confirm-delete').on('click', function (e) {
    e.preventDefault();
    var path = $(this).data('path');
    $('#deleteModal').data('path', path);
});
$('#btnYes').click(function () {
    location.href = $('#deleteModal').data('path');
});
function reload_form(obj) {
    console.log($(obj));
    loading_show();
    $.ajax({
        url  : "/app_dev.php/admin/objects/update",
        type : "POST",
        data : $(obj).closest('form').serialize(),
        success : function (data) {
            $('#form').html(data);
            loading_hide();
        }
    });
}
$(document).on("change", ".changeable", function () { reload_form(this)});

$(document).on("click", ".input-group-addon", function () {
    loading_show();
    var id = $(this).closest('div').data('id');
    var title = $(this).closest('div').find('.im_title').val();
    $.ajax({
        url  : "/admin/objects/imgedit",
        type : "POST",
        data : {'id':id, 'title':title},
        success : function () {
            loading_hide();
        }
    });
});

$(document).on("change", "select:not([multiple]).filter_change", function (e) {
    loading_show();
    e.preventDefault();
    var form = $(this).closest('form');
    $.ajax({
        url: window.location.href.split('?')[0],
        type: "POST",
        data: $(form).serialize(),
        success: function (data) {
            $('#page-wrapper').html($(data).find('#page-wrapper').children());
            loading_hide();
        },
        error: function () {
            loading_hide();
        }
    });
});
$(document).on("change", "select[multiple].filter_change", function (e) {
    loading_show();
    e.preventDefault();
    var form = $(this).closest('form');
    $.ajax({
        url: window.location.href.split('?')[0],
        type: "POST",
        data: $(form).serialize(),
        success: function (data) {
            $('#page-wrapper').html($(data).find('#page-wrapper').children());
            loading_hide();
        },
        error: function () {
            loading_hide();
        }
    });
});
$(document).on("change", ".select-change-form", function (e) {
    loading_show();
    e.preventDefault();
    var select = $(this);
    var value = select.val();
    var key = select.data('key');
    var data = {};
    data[key] = value;
    if (value) {
        $.ajax({
            url: select.data('path'),
            type: "GET",
            data: data,
            success: function (data) {
                $('#page-wrapper').html($(data).find('#page-wrapper').children());
                loading_hide();
            },
            error: function () {
                loading_hide();
            }
        });
    }
});
$(document).on("change", "#on_page", function (e) {
    $('body,html').animate({scrollTop: 0}, 200);
    loading_show();
    e.preventDefault();
    var form = $('#filter').closest('form');
    $.ajax({
        url: window.location.href.split('?')[0] + '?on_page=' + $(this).val(),
        type: "POST",
        data: $(form).serialize(),
        success: function (data) {
            $('#page-wrapper').html($(data).find('#page-wrapper').children());
            loading_hide();
        },
        error: function () {
            loading_hide();
        }
    });
});
$(document).on("click", ".asc, .desc, .knp-pagination a, .sortable", function (e) {
    $('body,html').animate({scrollTop: 0}, 200);
    loading_show();
    e.preventDefault();
    var form = $('#filter').closest('form');
    $.ajax({
        url: $(this).attr('href'),
        type: "POST",
        data: $(form).serialize(),
        success: function (data) {
            $('#page-wrapper').html($(data).find('#page-wrapper').children());
            loading_hide();
        },
        error: function () {
            loading_hide();
        }
    });
});
$(document).on("keypress", ".items_search", function (e) {
    if (e.which === 13) {
        $('#items_search').click();
        return false;
    }
});
$(document).on("click", "#items_search", function (e) {
    loading_show();
    e.preventDefault();
    location.href = window.location.href.split('?')[0] + '?query=' + $('.items_search').val();
    loading_hide();
});
function loading_show() {
    $('.loading').show();
}
function loading_hide() {
    $('.loading').hide();
}