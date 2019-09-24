

function alertSiteFile() {

	alert(event.target.nodeName);
}

function checkSiteFile(file_input_id) {

	document.getElementById("submit").disabled = false;
}

function mandatoryCheckBox() {

	document.getElementById("submit").disabled = false;
}

function Hide (addr) { document.getElementById(addr).style.visibility = "hidden";	}
function Show (addr) { document.getElementById(addr).style.visibility = "visible";	}

function toggle(anId) {

	if (document.getElementById(anId).style.visibility == "hidden")
	{	Show(anId);	}
	else															
	{	Hide(anId);	}
}

window.onload = function () { Hide("Type of integration");	};

function yesnoCheck() {
    if (document.getElementById('yesCheck').checked) {
        document.getElementById('Type of integration').style.display = 'block';
    } else {
        document.getElementById('Type of integration').style.display = 'none';
    }
}

//A finir
function compareDate() {

	if (document.getElementById('last_date').value < document.getElementById('first_date').value)
	{

	}else{

	}
}