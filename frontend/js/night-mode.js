$(document).ready(() => {
    const d = new Date();
    const hour = d.getHours();
    const theme = localStorage.getItem('theme');
    if (theme === 'dark' || theme === 'light')
        switch_style(theme);
    else if ($("#dark_button").is(":checked") || (hour >= 21 || hour <= 8))
        switch_style("dark");
});

function switch_style(theme) {
    if (theme === "light") {
        document.body.classList.remove('dark');
        $(".form-control").removeClass("dark");
        $(".navbar").removeClass("dark");
        $(".custom-file-label").removeClass("dark");
        $("#max-views").removeClass("dark");
        $("#upload-password").removeClass("dark");
        $("#dark_button").prop('checked', false);
    } else if (theme === "dark") {
        document.body.classList.add("dark");
        $(".form-control").addClass("dark");
        $(".navbar").addClass("dark");
        $(".custom-file-label").addClass("dark");
        $("#upload-password").addClass("dark");
        $("#max-views").addClass("dark");
        $("#dark_button").prop('checked', true);
    } else {
        if ($("#dark_button").is(':checked')) switch_style('dark');
        else switch_style('light');
    }
}