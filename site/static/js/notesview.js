function reloadView ()
{
	bdiv = document.getElementById("body");
	$.post(aBaseURL+"ajitem", { iID: itemID },
		function (data,status,xhr) {
			if (data) { bdiv.innerHTML = data; }
			else { alert("no data"); }
		}
	);
}
function fup_done (rslt)
{
	if (!rslt) $('#filupld').hide();
//	reloadView();
	$("#attachments").load(aBaseURL+"attlist",{ "contentID": contentID });
}
function getAttach (evt, elm, down)
{
	evt.preventDefault();
	var afile = elm.parentNode.dataset.afile;
	if (down) {
		var dlf = document.getElementById("dnldf");
		dlf.src = "index.php?option=com_usernotes&unID="+notesID+"&view=atvue&format=raw&cat="+contentID+"|"+afile+"&down=1";
	} else {
		window.location = "index.php?option=com_usernotes&unID="+notesID+"&view=atvue&format=raw&cat="+contentID+"|"+afile;
	}
}
function moveTo (evt)
{
	evt.preventDefault();
	ddlog = document.createElement("div");
	ddlog.className = "utildlog";
	ddlog.style.top = (evt.pageY - 100)+'px';
	ddlog.style.left = (evt.pageX + 30)+'px';
	document.body.appendChild(ddlog);
	$.post(aBaseURL+"cat_hier", { iID: itemID, pID: parentID },
		function (data,status,xhr) {
			if (data) { ddlog.innerHTML = data; }
			else { alert("no data"); }
		}
	);
	return false;
}
function addAttach (evt)
{
	evt.preventDefault();
	Oopim.Upld5d.Init();
	$('#filupld').show();
}
function doMove (doit)
{
	if (doit) {
		//alert($(\'#moveTo\').val());
		$.post(aBaseURL+"movitm", { iID: itemID, pID: $('#moveTo').val() },
			function (data,status,xhr) {
				if (data) { alert(data); }
				else { window.location.reload(); }
			}
		);
	}
	document.body.removeChild(ddlog);
}
function toolAct (evt,act)
{
	mclose();
	if ($(evt.srcElement).attr("data-sure")) {
		if (!confirm('Are you sure that you want to '+$(evt.srcElement).attr("data-sure")+'?')) return;
	}
	evt.preventDefault;
	$.post(aBaseURL+"tool", { mnuact: act, iID: itemID, cID: contentID },
		function (data,status,xhr) {
			if (data) { alert(data); }
			else { reloadView(); }
		}
	);
}
function toolMenu (evt)
{
	evt.preventDefault();	//console.log(evt);
	mopen('putmenu',evt.pageX+20,evt.pageY-8);
}
function dnldAttach (evt, wich)
{
	evt.preventDefault();
	var dlURL = aBaseURL+'adnld/' + contentID + '/' +wich.rel;
	//alert(dlURL); return;
	var dlframe = document.createElement("iframe");
	// set source to desired file
	dlframe.src = dlURL;
	// This makes the IFRAME invisible to the user.
	dlframe.style.display = "none";
	// Add the IFRAME to the page.  This will trigger the download
	document.body.appendChild(dlframe);
}
function printNote (evt,elm)
{
	evt.preventDefault();
	evt.stopPropagation();
	var newWindow = window.open(elm.href);
}
