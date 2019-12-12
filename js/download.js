$('#download-btn').click(function () {
    const id = $("#id").val();
    $("#download-form").attr('action', 'https://d.carlgo11.com/' + id + '/');
});

