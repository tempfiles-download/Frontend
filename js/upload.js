$('#upload-form').submit((e) => {
  e.preventDefault();

  let data = new FormData()
  if ($('#password').val() !== '') data.append('password', $('#password').val());
  data.append('file', $('input[type=file]')[0].files[0]);

  $.ajax({
    type: 'POST',
    url: 'https://api.tempfiles.download/upload/',
    dataType: 'JSON',
    cache: false,
    processData: false,
    contentType: false,
    data: data,
    success: (data, textStatus, xhr) => {
      //console.log(data);
      //console.log(xhr);
      $('#upload-form').hide();
      $('#title').text("File uploaded");
      $('#success').show();
      $('#url').val(data['url']);
      $('#deletion-password').val(data['deletepassword']);
      if ('password' in data) $('#server-password').val(data['password']);
      else $('#server-password').parent().hide();
      $('#url').select();
      document.execCommand("copy");
    }
  });
});
