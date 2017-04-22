var d = new Date();
var hour = d.getHours();
if (hour > 21 || hour < 8) {
    switch_style('dark');
} else {
    switch_style('bright');
}

// Credit to Christopher Heng, https://www.thesitewizard.com/javascripts/change-style-sheets.shtml
function switch_style(css_title)
{
    var i, link_tag;
    for (i = 0, link_tag = document.getElementsByTagName("link");
            i < link_tag.length; i++) {
        if ((link_tag[i].rel.indexOf("stylesheet") !== -1) &&
                link_tag[i].title) {
            link_tag[i].disabled = true;
            if (link_tag[i].title === css_title) {
                link_tag[i].disabled = false;
            }
        }
    }
}