/*
 *  Autogenerate a password if the user does not input anything.
 */

const passIn = document.getElementById('upload-password');
const buttonUpload = document.getElementById('upload-submit');
const form = document.getElementById('upload-form');
const passLen = 20;

buttonUpload.onclick = function() {
    
    if(passIn.value == "") {
        passIn.value = randomString(passLen);
        form.submit();
        //console.log('Submited with a generated password.');
    } else {
        form.submit();
        //console.log('Submited with a user-input password.');
    }

 };

randomString = function(length) {
    let text = "";
    const possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
    for(var i = 0; i < length; i++) {
        text += possible.charAt(Math.floor(Math.random() * possible.length));
    }
    return text;
}