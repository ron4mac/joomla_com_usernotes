/**
* @package		com_usernotes
* @copyright	Copyright (C) 2015-2022 RJCreations. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
*/
(function(UNote, $) {

	var estop = (e, sp=false) => {
		if (sp && e.stopPropagation) e.stopPropagation();
		if (e.preventDefault) e.preventDefault();
		e.returnValue = false;
	};

	var toFormData = (obj) => {
		const formData = new FormData();
		Object.keys(obj).forEach(key => {
			if (typeof obj[key] !== 'object') formData.append(key, obj[key]);
			else formData.append(key, JSON.stringify(obj[key]));
		});
		return formData;
	};

	var postAction = (task, parms={}, cb=()=>{}, json=false) => {
		if (typeof parms === 'object') {
			if (!(parms instanceof FormData)) parms = toFormData(parms);
		} else if (typeof parms === 'string') {
			parms = new URLSearchParams(parms);
		}
		if (task) parms.set('task', task);
	
		fetch(UNote.V.aBaseURL, {method:'POST',body:parms})
		.then(resp => { if (!resp.ok) throw new Error(`HTTP ${resp.status}`); if (json) return resp.json(); else return resp.text() })
		.then(data => cb(data))
		.catch(err => alert('Failure: '+err));
	};

	UNote.newFolderDlg = (evt, elm) => {
		estop(evt, true);
		$('#foldercr-modal').modal("show");
		return false;
	};

	UNote.edtFolderDlg = (evt, elm) => {
		estop(evt, true);
		$('#foldered-modal').modal("show");
		return false;
	};

	UNote.newFolder = (evt, elm) => {
		estop(evt);
	//	console.log(document.forms.un_newfold);
		postAction('edit.saveFolder', new FormData(document.forms.un_newfold), () => {$('#foldercr-modal').modal("hide");window.location.reload(true)});
		return false;
	};

	UNote.saveFolder = (evt, elm) => {
		estop(evt);
	//	console.log(document.forms.un_newfold);
		postAction('edit.saveFolder', new FormData(document.forms.un_edtfold), () => {$('#foldered-modal').modal("hide");window.location.reload(true)});
		return false;
	};

	UNote.performSearch = (aform) => {
		let sterm = $.trim(aform.sterm.value);
		if (sterm==='') {
			alert(UNote.L.no_sterm);
			return false;
		}
		aform.submit();
		return false;
	};


	UNote.aj_delAttach = (evt,cid,fn) => {
		estop(evt);
		let bURL = UNote.V.aBaseURL+'&task=';
		if (!confirm(UNote.L.sure_del_att)) return;
		postAction('detach', { contentID: cid, file: fn }, (data) => {
			if (data) { alert(data); }
			else { $("#attachments").load(bURL+"attlist&inedit=1",{ contentID: cid }); }
		});
	};


	UNote.aj_renAttach = (evt,cid,fn) => {
		estop(evt);
		let bURL = UNote.V.aBaseURL+'&task=';
		let nnam = prompt(UNote.L.rename_att, fn);
		if (!nnam) return;
		postAction('renAttach', { contentID: cid, file: fn, tofile: nnam }, (data) => {
			if (data) { alert(data); }
			else { $("#attachments").load(bURL+"attlist&inedit=1",{ contentID: cid }); }
		});
	};


	UNote.sprintf = (format, ...args) => {
		for (let i = 0; i < args.length; i++) {
			format = format.replace(/%s/, args[i]);
		}
		return format;
	};


	UNote.reloadView = () => {
		bdiv = document.getElementById("body");
		postAction('ajitem', { iID: UNote.V.itemID }, (data) => {
			if (data) { bdiv.innerHTML = data; }
			else { alert("no data"); }
		});
	};


	UNote.fup_done = (rslt) => {
		if (!rslt) $('#filupld').hide();
		$("#attachments").load(UNote.V.aBaseURL+"&task=attlist",{ contentID: UNote.V.contentID });
	};


	UNote.getAttach = (evt, elm, down) => {
		estop(evt,true);
		let afile = elm.parentNode.dataset.afile;
		if (down) {
			let dlf = document.getElementById("dnldf");
			dlf.src = UNote.V.aBaseURL+"&view=atvue&format=raw&cat="+UNote.V.contentID+"|"+afile+"&down=1";
		} else {
			window.location = UNote.V.aBaseURL+"&view=atvue&format=raw&cat="+UNote.V.contentID+"|"+afile;
		}
	};


	UNote.moveTo = (evt) => {
		estop(evt);
		ddlog = document.createElement("div");
		ddlog.className = "utildlog";
		ddlog.style.top = (evt.pageY - 100)+'px';
		ddlog.style.left = (evt.pageX + 30)+'px';
		document.body.appendChild(ddlog);
		postAction('cat_hier', { iID: UNote.V.itemID, pID: UNote.V.parentID }, (data) => {
			if (data) { ddlog.innerHTML = data; }
			else { alert("no data"); }
		});
		return false;
	};


	UNote.addAttach = (evt) => {
		estop(evt);
		UNote.Upld5d.Init();
		$('#filupld').show();
	};


	UNote.doMove = (doit) => {
		if (doit) {
			//alert($(\'#moveTo\').val());
			postAction('movitm', { iID: UNote.V.itemID, pID: $('#moveTo').val() }, (data) => {
					if (data) { alert(data); }
					else { window.location.reload(); }
			});
		}
		document.body.removeChild(ddlog);
	};


	UNote.addRating = (val,cbk) => {
		postAction('addRating', { rate: val, iID: UNote.V.itemID }, cbk);
	};


	UNote.toolAct = (evt,act) => {
		mclose();
		if ($(evt.srcElement).attr("data-sure")) {
			if (!confirm(UNote.sprintf(UNote.L.ru_sure, $(evt.srcElement).attr('data-sure')))) return;
		}
		estop(evt);
		postAction('tool', { mnuact: act, iID: UNote.V.itemID, cID: UNote.V.contentID }, (data) => {
			if (data) { alert(data); }
			else { reloadView(); }
		});
	};


	UNote.toolMenu = (evt) => {
		estop(evt);
		mopen('putmenu',evt.pageX+20,evt.pageY-8);
	};


	UNote.dnldAttach = (evt, wich) => {
		estop(evt);
		let dlURL = UNote.V.aBaseURL+'adnld/' + UNote.V.contentID + '/' +wich.rel;
		//alert(dlURL); return;
		let dlframe = document.createElement("iframe");
		// set source to desired file
		dlframe.src = dlURL;
		// This makes the IFRAME invisible to the user.
		dlframe.style.display = "none";
		// Add the IFRAME to the page.  This will trigger the download
		document.body.appendChild(dlframe);
	};


	UNote.printNote = (evt,elm) => {
		estop(evt);
		let newWindow = window.open(elm.href);
	};


	//----- small popup menu -----
	let pum_closetimer = null;
	let pum_menuitem = 0;
	// open hidden layer
	var mopen = (id, xpos, ypos, to=2500) => {
		// cancel close timer
		UNote.mcancelclosetime();
		// close old layer
		if (pum_menuitem) pum_menuitem.style.display = 'none';
		// get new layer and show it
		pum_menuitem = document.getElementById(id);
		pum_menuitem.style.left = xpos+'px';
		pum_menuitem.style.top = ypos+'px';
		pum_menuitem.style.display = 'block';
		UNote.mclosetime(to);
	};

	// close showed layer
	var mclose = () => {
		if (pum_menuitem) pum_menuitem.style.display = 'none';
	};

	// go close timer
	UNote.mclosetime = (to) => {
		pum_closetimer = window.setTimeout(mclose, to);
	};

	// cancel close timer
	UNote.mcancelclosetime = () => {
		if (pum_closetimer) {
			window.clearTimeout(pum_closetimer);
			pum_closetimer = null;
		}
	};



	$(document).ready( () => {
		$(document).on("touchstart touchmove touchend click", "a.act", function(ev) {	console.log(ev.type+':'+this.pending);
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

})(window.UNote = window.UNote || {}, jQuery);

if (typeof Joomla != "undefined")
	Joomla.submitbutton = function (butt) {
		let bp = butt.split('.');
		if (bp[1] == "cancel") return true;
		Joomla.submitform(butt);
	};
