$('#delete-form').submit((e) => {
  e.preventDefault();

  $.ajax({
    type: "DELETE",
    url: 'https://api.tempfiles.download/delete/?' + $.param({'id': $('#id').val(), 'p': $('#password').val(), 'delete': $('#del-pass').val()}),
    success: (data, textStatus, xhr) => {
      $('#delete-form').hide();
      $('#success').show();
      $('#title').text("File Deleted");
    }
  });
});
