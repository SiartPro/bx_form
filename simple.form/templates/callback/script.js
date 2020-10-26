$(function () {
    $('#callback_form').on('submit', function (event) {
        event.stopPropagation();

        var form = $(this);
        var obj = form.serialize();
        $.ajax({
            url: '/',
            dataType: "json",
            data: obj,
            async: false,
            type: "post",
            success: function (ans) {
                if (ans.STATUS) {
                    showModalAns()
                    form.trigger('reset');

                } else {
                    ans.ERROR.forEach(function (item) {
                        console.log(item);
                    });
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.log(textStatus);
                console.log(errorThrown);
            }
        });

        return false;
    });
});

// рекапча
function onCallbackSubmit(token) {
    $("#callback_form").trigger('submit');
}
