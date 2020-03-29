ondragover = ondragenter = (e) => {
    e.stopPropagation();
    e.preventDefault();
};

ondrop = (e) => {
    e.stopPropagation();
    e.preventDefault();
    file.files = e.dataTransfer.files;
    file.dispatchEvent(new Event('change'));
};

$('#back-btn').click(() => location.reload());

$('#upload-submit').click(() => {
    upload_file();
    return false;
});

$('#upload-form').submit(() => {
    upload_file();
    return false;
});

$('.select').click(function () {
    this.select()
});

$('#settings-button').click(() => {
    $('#settings-btn-icon').toggleClass('fa-angle-down fa-angle-up');
});

$('#max-views').on("change mousemove", function () {
    if ($(this).val() == 26) {
        $("#max-views-text").text("Infinite");
    } else
        $('#max-views-text').text($(this).val());
});

function upload_file() {
    const form_data = new FormData();

    if ($('#file').val() !== '')
        form_data.append('file', $('input[type=file]')[0].files[0]);
    if ($('#upload-password').val() !== '')
        form_data.append('password', $('#upload-password').val());
    if ($('#max-views').val() !== '0')
        form_data.append('maxviews', $('#max-views').val());

    $.ajax({
        type: 'POST',
        url: 'https://api.tempfiles.download/upload/',
        dataType: false,
        processData: false,
        contentType: false,
        cache: false,
        data: form_data,
        beforeSend: () => $('.upload-btn').toggle(),
        success: (data) => {
            $('.upload-btn').toggle();
            if (data['success'] === true) { //successful upload
                $('#upload-form').hide();
                $('#title-upload-file').hide();
                $('#error').hide();
                $('#url').val(data['url']);
                $('#id').val(data['id']);
                $('#deletionpass').val(data['deletepassword']);
                $('#upload_success').show();
            } else { //unsuccessful upload
                $('#error-msg').text('Error: ' + data['error']);
                $('#error').slideDown();
            }
        },
        error: (data) => {
            $('#upload-submit').show();
            $('#uploading-btn').hide();
            if (typeof data['responseJSON']['error'] !== 'undefined')
                $('#error-msg').text('Error: ' + data['responseJSON']['error']);
            else
                $('#error-msg').text('Error: ' + data['responseText']);
            $('#error').slideDown();
        }
    });
}
