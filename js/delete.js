---
---
$('#delete-form').submit((e) => {
  e.preventDefault();

  $.ajax({
    type: "DELETE",
    url: '{{ site.urls.delete }}?' + $.param({'id': $('#id').val(), 'delete': $('#del-pass').val()}),
    success: (data, textStatus, xhr) => {
      $('#delete-form').hide();
      $('#success').show();
      $('#title').text("File Deleted");
    }
  });
});
