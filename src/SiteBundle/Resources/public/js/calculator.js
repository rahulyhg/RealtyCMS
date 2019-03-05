(function($){
    $(document).ready(function(){
        calc();
        $('.calculator_form input').on('change', function() {
            calc();
        });
    });
})(jQuery);

function calc() {
    var price = $('#price').val() ? parseInt($('#price').val()) : 0;
    var first = $('#first').val() ? parseInt($('#first').val()) : 0;
    var years = $('#years').val() ? parseInt($('#years').val()) : 0;
    var procent = $('#procent').val() ? $('#procent').val() : 0;
    var sum = price-first;
    var result = getPayment(sum, years, procent);
    $('#calc-result').html(result);
}

function getPayment(sum, period, rate) {
    var i, koef, result;
    i = (rate / 12) / 100;
    koef = (i * (Math.pow(1 + i, period * 12))) / (Math.pow(1 + i, period * 12) - 1);
    result = sum * koef;
    return result.toFixed();
};