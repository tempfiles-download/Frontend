//$("#navbar-container").load("/res/content/navbar.html");
$(document).ready(function(e) {
  $('#upload-submit').click(function() {
    upload_file();
    return false;
  });

  $('#upload-form').submit(function() {
    upload_file();
    return false;
  });

  $('.select').click(function(){
    this.select();
  });

  function upload_file() {
    console.log("kek");
    var form_data = new FormData();
    if ($('#file').val() != "") {
      form_data.append('file', $('input[type=file]')[0].files[0]); //$file
      var file_data = $('#sortpicture').prop('files')[0];
    }
    form_data.append('password', $('#upload-password').val());
    console.log($('#file').val());
    console.log(...form_data);
    $.ajax({
      type: "POST",
      url: '/api/upload',
      dataType: 'text',
      processData: false,
      contentType: false,
      cache: false,
      data: form_data,
      success: function(data) {
        if (data['success'] === true) { //valid login
          console.log(data)
          $('#upload-form').hide();
          $('#error').hide();
          $('#upload_success').removeAttr('hidden');
          $('#url').val(data['url']);
          $('#id').val(data['id']);
          $('#deletionpass').val(data['deletepassword']);
        } else { //invalid login.
          console.log(data);
          $('#error').removeAttr('hidden');
          $('#error-msg').text('Error: ' + data['error']);
        }
      }
    });
  }
});
