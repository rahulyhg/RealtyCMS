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
$(document).on("change", "#objects_town_id", function () {
    $('.loading').show();
    $('#objects_area_id').val('');
    $.ajax({
        url  : "/admin/objects/update",
        type : "POST",
        data : $(this).closest('form').serialize(),
        success : function (data) {
            $('#objects_area_id').closest('div').html($(data).find('#objects_area_id'));
            $('.loading').hide();
        }
    });
});

$(document).on("click", ".input-group-addon", function () {
    $('.loading').show();  
    var id = $(this).closest('div').data('id');
    var title = $(this).closest('div').find('.im_title').val();
    $.ajax({
        url  : "/app_dev.php/admin/objects/imgedit",
        type : "POST",
        data : {'id':id, 'title':title},
        success : function (data) {            
            $('.loading').hide();
        }
    });
});