$('#download-form').submit((e) => {
  e.preventDefault();
  window.location.replace(`https://d.tempfiles.download/${$('#id').val()}/?p=${$('#password').val()}`);
});
