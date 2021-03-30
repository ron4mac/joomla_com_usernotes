UNote = {};	// a namespace for our javascript

(function($) {

	function estop (e, sp=false) {
		if (sp && e.stopPropagation) e.stopPropagation();
		if (e.preventDefault) e.preventDefault();
		e.returnValue = false;
	}

	UNote.performSearch = function (aform) {
		var sterm = $.trim(aform.sterm.value);
		if (sterm==='') {
			alert(this.L.no_sterm);
			return false;
		}
		aform.submit();
		return false;
	};


	UNote.aj_delAttach = function (evt,cid,fn) {
		estop(evt);
		var bURL = this.V.aBaseURL;
		if (!confirm(this.L.sure_del_att)) return;
		$.post(bURL+"detach", { contentID: cid, file: fn },
			function (data,status,xhr) {
				//console.log(xhr);
				if (data) { alert(data); }
				else { $("#attachments").load(bURL+"attlist&inedit=1",{ contentID: cid }); }
			}
		);
	};


	UNote.aj_renAttach = function (evt,cid,fn) {
		estop(evt);
		var bURL = this.V.aBaseURL;
		var nnam = prompt(this.L.rename_att, fn);
		if (!nnam) return;
		$.post(bURL+"renAttach", { contentID: cid, file: fn, tofile: nnam },
			function (data,status,xhr) {
				//console.log(xhr);
				if (data) { alert(data); }
				else { $("#attachments").load(bURL+"attlist&inedit=1",{ contentID: cid }); }
			}
		);
	};


	UNote.sprintf = function (format) {
		for (var i = 1; i < arguments.length; i++) {
			format = format.replace( /%s/, arguments[i] );
		}
		return format;
	};


	UNote.reloadView = function () {
		bdiv = document.getElementById("body");
		$.post(this.V.aBaseURL+"ajitem", { iID: this.V.itemID },
			function (data,status,xhr) {
				if (data) { bdiv.innerHTML = data; }
				else { alert("no data"); }
			}
		);
	};


	UNote.fup_done = function (rslt) {
		if (!rslt) $('#filupld').hide();
	//	reloadView();
		$("#attachments").load(this.V.aBaseURL+"attlist",{ "contentID": this.V.contentID });
	};


	UNote.getAttach = function (evt, elm, down) {
		estop(evt,true);
		var afile = elm.parentNode.dataset.afile;
		if (down) {
			var dlf = document.getElementById("dnldf");
			dlf.src = this.V.aBaseURL+"&view=atvue&format=raw&cat="+this.V.contentID+"|"+afile+"&down=1";
		} else {
			window.location = this.V.aBaseURL+"&view=atvue&format=raw&cat="+this.V.contentID+"|"+afile;
		}
	};


	UNote.moveTo = function (evt) {
		estop(evt);
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


	UNote.addAttach = function (evt) {
		estop(evt);
		this.Upld5d.Init();
		$('#filupld').show();
	};


	UNote.doMove = function (doit) {
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


	UNote.addRating = function (val,cbk) {
		$.post(this.V.aBaseURL+"addRating", { rate: val, iID: this.V.itemID },
			function (data,status,xhr) {
				if (data) { cbk(data); }
			//	else { reloadView(); }
			}
		);
	};


	UNote.toolAct = function (evt,act) {
		mclose();
		if ($(evt.srcElement).attr("data-sure")) {
			if (!confirm(this.sprintf(this.L.ru_sure, $(this).attr('data-suremsg')))) return;
		}
		estop(evt);
		$.post(this.V.aBaseURL+"tool", { mnuact: act, iID: this.V.itemID, cID: this.V.contentID },
			function (data,status,xhr) {
				if (data) { alert(data); }
				else { reloadView(); }
			}
		);
	};


	UNote.toolMenu = function (evt) {
		estop(evt);
		mopen('putmenu',evt.pageX+20,evt.pageY-8);
	};


	UNote.dnldAttach = function (evt, wich) {
		estop(evt);
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


	UNote.printNote = function (evt,elm) {
		estop(evt);
		var newWindow = window.open(elm.href);
	};


	//----- small popup menu -----
	var pum_closetimer = null;
	var pum_menuitem = 0;
	// open hidden layer
	function mopen (id, xpos, ypos, to=2500) {
		// cancel close timer
		mcancelclosetime();
		// close old layer
		if (pum_menuitem) pum_menuitem.style.display = 'none';
		// get new layer and show it
		pum_menuitem = document.getElementById(id);
		pum_menuitem.style.left = xpos+'px';
		pum_menuitem.style.top = ypos+'px';
		pum_menuitem.style.display = 'block';
		mclosetime(to);
	}

	// close showed layer
	function mclose () {
		if (pum_menuitem) pum_menuitem.style.display = 'none';
	}

	// go close timer
	function mclosetime (to) {
		pum_closetimer = window.setTimeout(mclose, to);
	}

	// cancel close timer
	function mcancelclosetime () {
		if (pum_closetimer) {
			window.clearTimeout(pum_closetimer);
			pum_closetimer = null;
		}
	}



	$(document).ready(function() {
		$(document).on("touchstart touchmove touchend click", "a.nav", function(ev) {	console.log(ev.type+':'+this.pending);
			if (ev.type == 'touchmove') {
				if (this.pending) --this.pending;
				return true;
			}
			if (ev.handled !== true) {
				if (ev.type == 'click' || (ev.type == 'touchend' && this.pending)) {
					ev.stopPropagation();
					ev.preventDefault();
					this.pending = 0;
					ev.handled = true;
					if ($(this).hasClass('sure') && !confirm(UNote.sprintf(UNote.L.ru_sure, $(this).attr('data-suremsg')))) return false;
					location.href = this.href;
				} else {
					if (ev.type == 'touchstart') this.pending = 10;
				}
			} else {
				return false;
			}
		});
	});

})(jQuery);

if (typeof Joomla != "undefined")
	Joomla.submitbutton = function (butt) {
		var bp = butt.split('.');
		if (bp[1] == "cancel") return true;
		Joomla.submitform(butt);
	};
