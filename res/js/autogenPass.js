/*
 *  Autogenerate a password if the user does not input anything.
 */
var passLen = Math.random() * (32 - 5) + 5;

$('#upload-submit').click(function () {
    if (!$('#upload-password').val()) {
        $('#upload-password').val(randomString(passLen));
        $("#upload-form").submit();
    } else {
        $("#upload-form").submit();
    }
});

function randomString(length) {
    var text = "";
    var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
    for (var i = 0; i < length; i++) {
        text += possible.charAt(Math.floor(Math.random() * possible.length));
    }
    return text;
}
