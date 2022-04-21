/**
* @package		com_usernotes
* @copyright	Copyright (C) 2015-2022 RJCreations. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
*/
(function(UNote) {

	let ddlog = null;
	let _celm = null;

	const _Id = (elm) => document.getElementById(elm);

	var estop = (e, sp=false) => {
		if (sp && e.stopPropagation) e.stopPropagation();
		if (e.preventDefault) e.preventDefault();
		e.returnValue = false;
	};

	// modal close for either J4 or J3
	var closMdl = (eid) => {
		let elm = _Id(eid);
		elm.close ? elm.close() : jQuery("#"+eid).modal('hide');
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


	UNote.newFolder = (evt, elm) => {
		estop(evt);
		let fm = document.forms.un_newfold;
		if (document.formvalidator.isValid(fm))
			postAction('edit.saveFolder', new FormData(fm), (rd) => {closMdl('foldercr-modal'); if (rd) {alert(rd);return;} window.location.reload(true)});
		return false;
	};


	UNote.saveFolder = (evt, elm) => {
		estop(evt);
		let fm = document.forms.un_edtfold;
		if (document.formvalidator.isValid(fm))
			postAction('edit.saveFolder', new FormData(fm), (rd) => {closMdl('foldered-modal'); if (rd) {alert(rd);return;} window.location.reload(true)});
		return false;
	};


	UNote.performSearch = (aform) => {
		let sterm = aform.sterm.value.trim();
		if (sterm==='') {
			alert(UNote.L.no_sterm);
			return false;
		}
		aform.submit();
		return false;
	};


	UNote.aj_delAttach = (evt, cid, fn) => {
		estop(evt);
		if (!confirm(UNote.L.sure_del_att)) return;
		postAction('detach', { contentID: cid, file: fn }, (data) => {
			if (data.err) { alert(data.err); }
			else { _Id("attachments").innerHTML = data.htm; }
		}, true);
	};


	UNote.aj_renAttach = (evt, cid, fn) => {
		estop(evt);
		let nnam = prompt(UNote.L.rename_att, fn);
		if (!nnam) return;
		postAction('renAttach', { contentID: cid, file: fn, tofile: nnam }, (data) => {
			if (data.err) { alert(data.err); }
			else { _Id("attachments").innerHTML = data.htm; }
		}, true);
	};


	UNote.sprintf = (format, ...args) => {
		for (let i = 0; i < args.length; i++) {
			format = format.replace(/%s/, args[i]);
		}
		return format;
	};


	UNote.reloadView = () => {
		let bdiv = _Id("body");
		postAction('ajitem', { iID: UNote.V.itemID }, (data) => {
			if (data) { bdiv.innerHTML = data; }
			else { alert("no data"); }
		});
	};


	UNote.fup_done = (rslt) => {
		if (!rslt) _Id('filupld').style.display = "none";
		postAction('attlist', { contentID: UNote.V.contentID }, (data) => {
			if (data) { _Id("attachments").innerHTML = data; }
			else { alert("no data"); }
		});
	};


	UNote.getAttach = (evt, elm, down) => {
		estop(evt,true);
		let afile = elm.parentNode.dataset.afile;
		if (down) {
			let dlf = _Id("dnldf");
			dlf.src = UNote.V.aBaseURL+"&view=atvue&format=raw&cat="+UNote.V.contentID+"|"+afile+"&down=1";
		} else {
			window.location = UNote.V.aBaseURL+"&view=atvue&format=raw&cat="+UNote.V.contentID+"|"+afile;
		}
	};


	UNote.moveTo = (evt) => {
		estop(evt);
		ddlog = document.createElement("div");
		ddlog.className = "utildlog";
		_celm.appendChild(ddlog);
		postAction('cat_hier', { iID: UNote.V.itemID, pID: UNote.V.parentID }, (data) => {
			if (data) { ddlog.innerHTML = data; }
			else { alert("no data"); }
		});
		return false;
	};


	UNote.addAttach = (evt) => {
		estop(evt);
		UNote.Upld5d.Init();
		_Id('filupld').style.display = "block";
	};


	UNote.doMove = (doit) => {
		if (doit) {
			postAction('movitm', { iID: UNote.V.itemID, pID: _Id('moveTo').value }, (data) => {
					if (data) { alert(data); }
					else { window.location.reload(); }
			});
		}
		_celm.removeChild(ddlog);
		ddlog = null;
	};


	UNote.addRating = (val, cbk) => {
		postAction('addRating', { rate: val, iID: UNote.V.itemID }, cbk);
	};


	UNote.toolAct = (evt, act) => {
		mclose();
		if (evt.target.dataset.sure) {
			if (!confirm(UNote.sprintf(UNote.L.ru_sure, evt.target.dataset.sure))) return;
		}
		estop(evt);
		postAction('tool', { mnuact: act, iID: UNote.V.itemID, cID: UNote.V.contentID }, (data) => {
			if (data) { alert(data); }
			else { window.location.reload(); }
		});
	};


	UNote.toolMenu = (evt) => {
		estop(evt);
		mopen('putmenu',evt.pageX-60,evt.pageY-4);
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
		// Add the IFRAME to the page. This will trigger the download
		document.body.appendChild(dlframe);
	};


	UNote.printNote = (evt, elm) => {
		estop(evt, true);
		window.open(elm.href);
	};


	//----- small popup menu -----
	let pum_closetimer = null;
	let pum_menuitem = 0;
	// open hidden layer
	var mopen = (id, xpos, ypos, to=3500) => {
		// cancel close timer
		UNote.mcancelclosetime();
		// close old layer
		if (pum_menuitem) pum_menuitem.style.display = 'none';
		// get new layer and show it
		pum_menuitem = _Id(id);
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

	// handle action item clicks
	const evtHandler = (e) => {
		//console.log(e);
		let se = e.target;
		if (se.classList.contains('sure')) {
			e.stopPropagation();
			e.preventDefault();
			if (!confirm(UNote.sprintf(UNote.L.ru_sure, se.dataset.suremsg))) return false;
			window.location.href = se.href;
		}
	};

	document.addEventListener('DOMContentLoaded', () => {
		_celm = _Id('container');
		_celm && _celm.addEventListener('click', evtHandler);
	});

})(window.UNote = window.UNote || {});

if (typeof Joomla != "undefined")
	Joomla.submitbutton = function (butt) {
		let bp = butt.split('.');
		if (bp[1] == "cancel") return true;
		Joomla.submitform(butt);
	};
