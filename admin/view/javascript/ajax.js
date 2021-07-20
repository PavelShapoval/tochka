(function ($) {
    $(document).ready(function () {
        $(document).on('click', '.start-test123', function (e) {
            e.preventDefault();
            $load_prodat = true;
            $load_pricat = true;

            $.ajax({
                //url: '/test.php',
                url: '/admin/controller/extension/module/products_insert.php',
                type: 'POST',
                dataType: 'json',
                data: $load_prodat,
                //data: {cart_item_key: cart_item_key, data_product_id: data_product_id}
            })
                .done(function(response) {
                    /*console.log("success");*/
                    console.log(response);
                    //count.text(response);
                })
                .success(function(){
                    alert('Данные успешно отправлены.');
                })
                .fail(function() {
                    console.log("error");
                })
                .always(function() {
                    console.log("complete");
                });
        });
    });
})(jQuery);