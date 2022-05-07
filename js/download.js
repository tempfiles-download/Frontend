---
---
$('#download-form').submit((e) => {
  e.preventDefault();
  window.location.replace(`{{ site.urls.download }}/${$('#id').val()}/?p=${$('#password').val()}`);
});
