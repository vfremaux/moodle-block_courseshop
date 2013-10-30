
function listClear(nonSelect) {
nonSelect.selectedIndex = 0;
}

function flowcontrol_toggle(wwwroot){
    var panel = document.getElementById('flowcontrol');
    if (panel.style.visibility == 'hidden'){
        panel.style.visibility = 'visible';
        panel.style.display = 'table';
        document.images['flowcontrol_button'].src = wwwroot + "/blocks/courseshop/pix/minus.png";
    } else {
        panel.style.visibility = 'hidden';
        panel.style.display = 'none';
        document.images['flowcontrol_button'].src = wwwroot + "/blocks/courseshop/pix/plus.png";
    }
}
