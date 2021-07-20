(function($){

    $(document).ready(function(){
        //форма 1 клик
        $('.button-click').on('click', function () {
            event.preventDefault();
            $('.layer, .form-wrap').fadeIn(100);
        });
        $('.form-wrap svg, .layer').on('click', function () {
            $('.form-wrap, .layer').fadeOut(100);
        });
        $('.form-alert1 svg, .layer').on('click', function () {
            $('.form-alert1, .layer').fadeOut(100);
        });
        $('.form-alert2 svg, .layer').on('click', function () {
            $('.form-alert2, .layer').fadeOut(100);
        });
        $('.form-input-submit').on('click', function(e){
            e.preventDefault();
            $.ajax({
                url: '/modform.php',
                type: 'POST',
                //dataType: 'json',
                data: $('.modform').serialize()
            })
                .success(function(data, status, xhr) {
                    /*console.log(1);*/
                    console.log(data, status, xhr);
                    $('.modform').trigger("reset");
                    grecaptcha.reset();
                    $('.form-wrap').fadeOut(100);
                    $('.form-alert1').fadeIn(100);

                })
                .error(function(data, status, xhr) {
                    /*console.log(2);
                    console.log(err);*/
                    $('.form-wrap').fadeOut(100);
                    $('.form-alert2').fadeIn(100);

                })
                .always(function() {
                    /*console.log("complete");*/

                });
        });
        $('.form-phone').inputmask({"mask": "9(999) 999-9999"}); //specifying options
        $(document).on("mouseover", '#input-payment-telephone',function (event) {
            $('#input-payment-telephone').inputmask({"mask": "9(999) 999-9999"});
        });
        $(document).on("mouseover", '#input-payment-email',function (event) {
            $("#input-payment-email").inputmask({ alias: "email"});
        });
        $(document).on("mouseover", '#input-email',function (event) {
            $("#input-email").inputmask({ alias: "email"});
        });
        $(document).on("mouseover", '#input-payment-postcode',function (event) {
            $("#input-payment-postcode").inputmask({"mask": "999999"});
        });
        $(document).on("mouseover", '#input-shipping-postcode',function (event) {
            $("#input-shipping-postcode").inputmask({"mask": "999999"});
        });
        if ($(window).width() <= '1199'){
            $('.parent-block').on('click', function () {
                $(this).find('.children-cats').fadeToggle(100);
            });
            $(document).mouseup(function (e){ // событие клика по веб-документу
                var div = $('#column-left .categories-list .children-cats'); // тут указываем ID элемента
                if (!div.is(e.target) // если клик был не по нашему блоку
                    && div.has(e.target).length === 0) { // и не по его дочерним элементам
                    div.hide(); // скрываем его
                }
            });
        }



    });
})(window.jQuery);