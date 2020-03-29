$('#download-btn').click(function () {
    const id = $("#id").val();
    $("#download-form").attr('action', 'https://d.tempfiles.download/' + id + '/');
});

const fourofourParam = new RegExp('[\?&]404=([^&#]*)').exec(window.location.href);
if (fourofourParam != null && JSON.parse(fourofourParam[1])) {
    $('#download-404').show();
}
