$(document).ready(function(e) {
  var d = new Date();
  var hour = d.getHours();
  if (hour >= 21 || hour <= 8) {
    switch_style();
  }

  function switch_style() {
    var body = document.body;
    body.classList.add("dark");
    $("#logo").attr("src", "/img/logo.svg");
    $(".form-control").addClass("dark");
    $("#upload-password").addClass("dark");
    $(".navbar").addClass("dark");
  }
});
