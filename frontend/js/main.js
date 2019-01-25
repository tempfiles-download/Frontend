---
---

ondragover = ondragenter = function (e) {
    e.stopPropagation();
    e.preventDefault();
};

ondrop = function (e) {
    e.stopPropagation();
    e.preventDefault()
    file.files = e.dataTransfer.files;
    file.dispatchEvent(new Event('change'));
}

$('#back-btn').click(function () {
    location.reload();
});

$('#dark_button').click(function () {
    if ($(this).is(':checked'))
        Cookies.set('theme', 'dark');
    else
        Cookies.set('theme', 'light');
    switch_style();
});

$('#upload-submit').click(function () {
    upload_file();
    return false;
});

$('#upload-form').submit(function () {
    upload_file();
    return false;
});

$('.select').click(function () {
    this.select();
});


function upload_file() {
    const form_data = new FormData();

    if ($('#file').val() != '') {
        form_data.append('file', $('input[type=file]')[0].files[0]); //$file
    }

    form_data.append('password', $('#upload-password').val());
    if ($('#max-views').val() != '0') {
        form_data.append('maxviews', $('#max-views').val());
    }

    setTimeout(function () {
        $.ajax({
            type: 'POST',
            url: '/api/upload',
            dataType: false,
            processData: false,
            contentType: false,
            cache: false,
            data: form_data,
            beforeSend: function () {
                $('.upload-btn').toggle();
            },
            success: function (data) {
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
                    $('#error').fadeIn('slow');

                }
            },
            error: function (data) {
                $('#upload-submit').show();
                $('#uploading-btn').hide();
                $('#error-msg').text('Error: ' + data['responseText']);
                $('#error').show('slow');
            }
        });
    }, 10);

}

$('#settings-button').click(function (e) {
    e.preventDefault();
    $('#advanced-settings').slideToggle("fast");
    $('#settings-btn-icon').toggleClass('fa-angle-down fa-angle-up');
    return false;
});

$(document).ready(function () {
    bsCustomFileInput.init();

    // if (window.matchMedia("(prefers-color-scheme: dark)").matches)
    //     switch_style("dark");

    var longhash = '{{ site.github.build_revision }}';
    if (longhash.length > 0) {
        var shorthash = longhash.slice(0, 7);
        $('#version').text(shorthash);
    }
});
