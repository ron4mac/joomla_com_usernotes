(function($) {

	Oopim.reloadView = function () {
		bdiv = document.getElementById("body");
		$.post(this.V.aBaseURL+"ajitem", { iID: this.V.itemID },
			function (data,status,xhr) {
				if (data) { bdiv.innerHTML = data; }
				else { alert("no data"); }
			}
		);
	};


	Oopim.fup_done = function (rslt) {
		if (!rslt) $('#filupld').hide();
	//	reloadView();
		$("#attachments").load(this.V.aBaseURL+"attlist",{ "contentID": this.V.contentID });
	};


	Oopim.getAttach = function (evt, elm, down) {
		evt.preventDefault();
		var afile = elm.parentNode.dataset.afile;
		if (down) {
			var dlf = document.getElementById("dnldf");
		//	dlf.src = "index.php?option=com_usernotes&unID="+notesID+"&view=atvue&format=raw&cat="+contentID+"|"+afile+"&down=1";
			dlf.src = "index.php?option=com_usernotes&view=atvue&format=raw&cat="+contentID+"|"+afile+"&down=1";
		} else {
		//	window.location = "index.php?option=com_usernotes&unID="+notesID+"&view=atvue&format=raw&cat="+contentID+"|"+afile;
			window.location = "index.php?option=com_usernotes&view=atvue&format=raw&cat="+contentID+"|"+afile;
		}
	};


	Oopim.moveTo = function (evt) {
		evt.preventDefault();
		ddlog = document.createElement("div");
		ddlog.className = "utildlog";
		ddlog.style.top = (evt.pageY - 100)+'px';
		ddlog.style.left = (evt.pageX + 30)+'px';
		document.body.appendChild(ddlog);
		$.post(this.V.aBaseURL+"cat_hier", { iID: this.V.itemID, pID: this.V.parentID },
			function (data,status,xhr) {
				if (data) { ddlog.innerHTML = data; }
				else { alert("no data"); }
			}
		);
		return false;
	};


	Oopim.addAttach = function (evt) {
		evt.preventDefault();
		this.Upld5d.Init();
		$('#filupld').show();
	};


	Oopim.doMove = function (doit) {
		if (doit) {
			//alert($(\'#moveTo\').val());
			$.post(this.V.aBaseURL+"movitm", { iID: this.V.itemID, pID: $('#moveTo').val() },
				function (data,status,xhr) {
					if (data) { alert(data); }
					else { window.location.reload(); }
				}
			);
		}
		document.body.removeChild(ddlog);
	};


	Oopim.toolAct = function (evt,act) {
		mclose();
		if ($(evt.srcElement).attr("data-sure")) {
			if (!confirm(this.sprintf(this.L.ru_sure, $(this).attr('data-suremsg')))) return;
		}
		evt.preventDefault;
		$.post(this.V.aBaseURL+"tool", { mnuact: act, iID: this.V.itemID, cID: this.V.contentID },
			function (data,status,xhr) {
				if (data) { alert(data); }
				else { reloadView(); }
			}
		);
	};


	Oopim.toolMenu = function (evt) {
		evt.preventDefault();	//console.log(evt);
		mopen('putmenu',evt.pageX+20,evt.pageY-8);
	};


	Oopim.dnldAttach = function (evt, wich) {
		evt.preventDefault();
		var dlURL = this.V.aBaseURL+'adnld/' + this.V.contentID + '/' +wich.rel;
		//alert(dlURL); return;
		var dlframe = document.createElement("iframe");
		// set source to desired file
		dlframe.src = dlURL;
		// This makes the IFRAME invisible to the user.
		dlframe.style.display = "none";
		// Add the IFRAME to the page.  This will trigger the download
		document.body.appendChild(dlframe);
	};


	Oopim.printNote = function (evt,elm) {
		evt.preventDefault();
		evt.stopPropagation();
		var newWindow = window.open(elm.href);
	};

})(jQuery);
