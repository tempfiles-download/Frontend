$(document).ready(() => {
    const longhash = '{{ site.github.build_revision }}';
    if (longhash.length > 0)
        $('#version').text(longhash.slice(0, 7));
});
