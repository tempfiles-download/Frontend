$(document).ready(() => {
    const longhash = '{{ site.github.build_revision }}';
    if (longhash.length > 0) {
        const shorthash = longhash.slice(0, 7);
        $('#version').text(shorthash);
    }
});
