$(document).ready(function() {
    $('#register_webhooks').submit(function( event ){
        event.preventDefault();
        $.ajax({
            url: $('#listener').val(),
            type: "get",
            success: function(){
                console.log("Updated Webhooks");
                location.reload();
            },
            error:function(){
                console.log("Failed Updating Webhooks");
            }
        });
    });
});