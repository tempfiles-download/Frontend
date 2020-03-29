$('#delete-form').submit((e) => {
    e.stopPropagation();
    e.preventDefault();

    const id = $("#id").val();
    const p = $("#p").val();
    const del = $("#delete").val();

    $.ajax({
        type: 'GET',
        url: 'http://api.tempfiles.download/delete/',
        data: {
            "id": id,
            "p": p,
            "delete": del,
        },
        cache: false,
        dataType: false,
        contentType: false,
        timeoutSeconds: 1,
        success: (response) => {
            console.log(response);
        }

    });
});

