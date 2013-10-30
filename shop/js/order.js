// Javascript functions for shop/order

function send_confirm(){
	document.forms['bill'].cmd.value = 'confirm';
	document.forms['bill'].view.value = 'success';
	document.forms['bill'].action = '/blocks/courseshop/shop/view.php';
	document.forms['bill'].target = '_self';
	document.forms['bill'].submit();
}

function listen_to_required_changes(){
	if (haverequireddata()){
		document.forms['confirmation'].elements['go_confirm'].disabled = false;
		advicediv = document.getElementById('courseshop-disabled-advice-span');
		advicediv.style.visibility = 'hidden';
	} else {
		document.forms['confirmation'].elements['go_confirm'].disabled = true;
		advicediv = document.getElementById('courseshop-disabled-advice-span');
		advicediv.style.visibility = 'visible';
	}
}

var requiredorderfieldlist = null;

function haverequireddata(){
	
	if (requiredorderfieldlist == null) return true;
	
	for (i = 0 ; i < requiredorderfieldlist.length ; i++) {
		if (document.forms['bill'].elements[requiredorderfieldlist[i]].value == '')
			return false;
	}
	
	return true;
}

function accept_eulas(buttonobj){
	if (buttonobj.form.agreeeula.checked){
		agreediv = document.getElementById('euladiv');
		agreediv.style.display = 'none';
		agreediv.style.visibiltiy = 'hidden';
		orderpanel = document.getElementById('orderpanel');
		orderpanel.style.display = 'block';
	}
}

