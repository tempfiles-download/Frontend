$(document).ready(function () {
    const d = new Date();
    const hour = d.getHours();
    const theme = localStorage.getItem('theme');
    if (theme === undefined) {
        if ($("#dark_button").is(":checked") || (hour >= 21 || hour <= 8))
            switch_style("dark");
    } else if (theme === 'dark' || theme === 'light') {
        switch_style(theme);
    }
});

function switch_style(theme) {
    if (theme === "light") {
        document.body.classList.remove('dark');
        $("#logo").attr("src", "/img/logo.svg");
        $(".form-control").removeClass("dark")
        $("#upload-password").removeClass("dark");
        $(".navbar").removeClass("dark");
        $("#dark_button").prop('checked', false);
    } else if (theme === "dark") {
        document.body.classList.add("dark");
        $("#logo").attr("src", "/img/logo_light.svg");
        $(".form-control").addClass("dark");
        $("#upload-password").addClass("dark");
        $(".navbar").addClass("dark");
        $("#dark_button").prop('checked', true);
    } else {
        const button = $("#dark_button");
        if (button.is(':checked'))
            switch_style('dark');
        else
            switch_style('light');
    }
}