
ondragover = ondragenter = (e) => {
  e.stopPropagation();
  e.preventDefault();
  $('#popup').show();
};

ondrop = (e) => {
  e.stopPropagation();
  e.preventDefault();
  file.files = e.dataTransfer.files;
  file.dispatchEvent(new Event('change'));
  $('#popup').hide();
};
