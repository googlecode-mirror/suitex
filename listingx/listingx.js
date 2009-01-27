function goToURL(url){
	window.location = url;
}

function confirmAction(actionText, url){
	if (confirm(actionText)){
		window.location = url;
	}
}

function openSub(subName, imageName, imagePath){
	var sub   = document.getElementById(subName);
	var image = document.getElementById(imageName);

	var plus  = imagePath + "/" + "plus.gif";
	var minus = imagePath + "/" + "minus.gif";


	if (sub){
		if (sub.style.display == 'none'){
			sub.style.display = '';
			image.src = minus;
		}
		else {
			sub.style.display = 'none';
			image.src = plus;
		}
	}
}
