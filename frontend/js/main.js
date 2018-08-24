---
---
$(document).ready(function(e) {
  $('#upload-submit').click(function() {
    upload_file();
    return false;
  });

  $('#upload-form').submit(function() {
    upload_file();
    return false;
  });

  $('.select').click(function() {
    this.select();
  });

  $('#settings-button').click(function(e){
    e.preventDefault();
    $('#advanced-settings').slideToggle();
    return false;
  });

  var longhash = '{{ site.github.build_revision }}'
  if (longhash.length > 0) {
    var shorthash = longhash.slice(0, 7);
    $('#version').text(shorthash);
  }

  function upload_file() {
    var form_data = new FormData();
    if ($('#file').val() != "") {
      form_data.append('file', $('input[type=file]')[0].files[0]); //$file
    }
    form_data.append('password', $('#upload-password').val());
    if($('#max-views').val() != "0"){
      form_data.append('maxviews', $('#max-views').val());
    }
    $.ajax({
      type: "POST",
      url: '/api/upload',
      dataType: false,
      processData: false,
      contentType: false,
      cache: false,
      data: form_data,
      success: function(data) {
        if (data['success'] === true) { //valid login
          $('#upload-form').hide();
          $('#error').hide();
          $('#upload_success').removeAttr('hidden');
          $('#url').val(data['url']);
          $('#id').val(data['id']);
          $('#deletionpass').val(data['deletepassword']);
        } else { //invalid login.
          console.log(data);
          $('#error').fadeIn("slow");
          $('#error-msg').text('Error: ' + data['error']);
        }
      }
    });
  }
});
