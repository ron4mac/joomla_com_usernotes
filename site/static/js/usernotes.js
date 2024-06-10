/**
* @package		com_usernotes
* @copyright	Copyright (C) 2015-2024 RJCreations. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
* @since		1.4.4
*/
'use strict';

(function(UNote) {

	let ddlog = null,
		_celm = null,
		curCelm = null,
		curNid = 0;

	/** @noinline */
	const _Id = elm => document.getElementById(elm);


	const estop = (e, sp=false) => {
		if (sp && e.stopPropagation) e.stopPropagation();
		if (e.preventDefault) e.preventDefault();
		e.returnValue = false;
	};


	// modal close for either J4 or J3
	const closMdl = (eid) => {
		let elm = _Id(eid);
		elm.close ? elm.close() : jQuery('#'+eid).modal('hide');
	};


	// open or close modals based on J4 or J3 bootstrap
	const _oM = (elm) => {
		if (typeof elm === 'string') elm = _Id(elm);
		elm.open ? elm.open() : jQuery(elm).modal('show');
	};
	const _cM = (elm) => {
		if (!elm) { Joomla.Modal.getCurrent().close(); return; }
		if (typeof elm === 'string') elm = _Id(elm);
		elm.close ? elm.close() : jQuery(elm).modal('hide');
	};


	const toFormData = (obj) => {
		const formData = new FormData();
		Object.keys(obj).forEach(key => {
			if (typeof obj[key] !== 'object') formData.append(key, obj[key]);
			else formData.append(key, JSON.stringify(obj[key]));
		});
		return formData;
	};


	const postAction = (task, parms={}, cb=()=>{}, json=false) => {
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
		return true;
	};


	UNote.deleteItem = (evt) => {
		let aform = document.forms.actForm;
		aform.task.value = 'edit.deleteItem';
		aform.submit();
		return false;
	};


	UNote.aj_delAttach = (evt, cid, fn) => {
		estop(evt);
		if (!confirm(UNote.L.sure_del_att)) return;
		postAction('edit.detach', { contentID: cid, file: fn }, (data) => {
			if (data.err) { alert(data.err); }
			else { _Id('attachments').innerHTML = data.htm; }
		}, true);
	};


	UNote.aj_renAttach = (evt, cid, fn) => {
		estop(evt);
		let nnam = prompt(UNote.L.rename_att, fn);
		if (!nnam) return;
		postAction('edit.renAttach', { contentID: cid, file: fn, tofile: nnam }, (data) => {
			if (data.err) { alert(data.err); }
			else { _Id('attachments').innerHTML = data.htm; }
		}, true);
	};


	UNote.sprintf = (format, ...args) => {
		for (let i = 0; i < args.length; i++) {
			format = format.replace(/%s/, args[i]);
		}
		return format;
	};


	UNote.fup_done = (rslt) => {
		if (!rslt) _Id('filupld').style.display = 'none';
		postAction('edit.attlist', { contentID: UNote.V.contentID, inedit: 1 }, (data) => {
			if (data) { _Id('attachments').innerHTML = data; }
			else { alert('no data'); }
		});
	};


	UNote.getAttach = (evt, elm, down) => {
		estop(evt,true);
		let afile = elm.parentElement.dataset.afile;
		let aurl = UNote.V.aBaseURL+'&view=atvue&cat='+UNote.V.itemID+'|'+UNote.V.contentID+'|'+afile;
		if (down) {
			let dlf = _Id('dnldf');
			dlf.src = aurl + '&down=1';
		} else {
		//	window.location = aurl;
			window.open(aurl, '_blank');
		}
	};


	UNote.moveTo = (evt) => {
		estop(evt);
		ddlog = document.createElement('div');
		ddlog.className = 'utildlog';
		_celm.appendChild(ddlog);
		postAction('edit.cat_hier', { iID: UNote.V.itemID, pID: UNote.V.parentID }, (data) => {
			if (data) { ddlog.innerHTML = data; }
			else { alert('no data'); }
		});
		return false;
	};


	UNote.addAttach = (evt) => {
		estop(evt);
		UNote.Upld5d.Init();
		_Id('filupld').style.display = 'block';
	};


	UNote.doMove = (doit) => {
		if (doit) {
			postAction('edit.movitm', { iID: UNote.V.itemID, pID: _Id('moveTo').value }, (data) => {
					if (data) { alert(data); }
					else { window.location.reload(); }
			});
		}
		_celm.removeChild(ddlog);
		ddlog = null;
	};


	UNote.addRating = (val, cbk) => {
		postAction('addRating', { rate: val, iID: UNote.V.itemID }, cbk, true);
	};


	UNote.toolAct = (evt, act) => {
		mclose();
		if (evt.target.dataset.sure) {
			if (!confirm(UNote.sprintf(UNote.L.ru_sure, evt.target.dataset.sure))) return;
		}
		estop(evt);
		postAction('edit.tool', { mnuact: act, iID: UNote.V.itemID, cID: UNote.V.contentID }, (data) => {
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
		let dlframe = document.createElement('iframe');
		// set source to desired file
		dlframe.src = dlURL;
		// This makes the IFRAME invisible to the user.
		dlframe.style.display = 'none';
		// Add the IFRAME to the page. This will trigger the download
		document.body.appendChild(dlframe);
	};


	UNote.printNote = (evt, elm) => {
		estop(evt, true);
		window.open(elm.href);
	};

	UNote.submitComment = (elm) => {
		elm.disabled = true;
		let fData = new FormData(newcmnt);
		fData.append('nid', curNid);
		postAction(null, fData, (data) => {
			closMdl('comment-modal');
			elm.disabled = false;
			//curCelm.classList.add('hasem');
			curCelm.innerHTML = data.htm;
		}, true);
	};

	UNote.deleteComment = (evt, cid) => {
		estop(evt);
		let fData = new FormData();
		fData.append('task', 'delComment');
		fData.append('cmntid', cid);
		fData.append(Joomla.getOptions('csrf.token'), 1);
		postAction(null, fData, (data) => {
			let celm = evt.target.parentElement.parentElement;
			celm.parentElement.removeChild(celm);
			if (data.htm) {
				curCelm.innerHTML = data.htm;
			}
		}, true);
	};

	UNote.fetchComments = (elm) => {
		elm.disabled = true;
		let fData = new FormData();
		fData.append('task', 'getComments');
		fData.append('nid', curNid);
		postAction(null, fData, (data) => {
			let cmnts = document.querySelector('#comments-modal .comments');
			cmnts.innerHTML = data.htm;
			let shDlg = _Id('comments-modal');
			_oM(shDlg);
			elm.disabled = false;
		}, true);
	};

	UNote.cmntNote = (evt, elm) => {
		estop(evt, true);
		curNid = UNote.V.itemID;
		curCelm = elm;
		if (elm.children[0].classList.contains('hasem')) {
			UNote.fetchComments(curCelm);
		} else {
			_Id('cmnt-text').value = '';
			let ncDlg = _Id('comment-modal');
			_oM(ncDlg);
		}
	};


	//----- small popup menu -----
	let pum_closetimer = null;
	let pum_menuitem = 0;
	// open hidden layer
	const mopen = (id, xpos, ypos, to=3500) => {
		// cancel close timer
		UNote.mcancelclosetime();
		// close old layer
		if (pum_menuitem) pum_menuitem.style.display = 'none';
		// get new layer and show it
		pum_menuitem = _Id(id);
		pum_menuitem.style.display = 'block';
		UNote.mclosetime(to);
	};


	UNote.qView = (elm) => {
		let nlnk = elm.parentElement.dataset.href;
		let dlg = _Id('qview-modal');
		let dttl = dlg.querySelector('.modal-title');
		dttl.innerHTML = '<a href="' + nlnk + '">' + elm.innerHTML + '</a>';
		_Id('qviewdata').innerHTML = 'Loading...';
		dlg.open ? dlg.open() : jQuery(dlg).modal('show');
		let parms = new URLSearchParams('qview=1');
		let hAtt = null;
		fetch(nlnk+'&format=raw', {method:'POST',body:parms})
		.then(resp => {
			if (!resp.ok) throw new Error(`HTTP ${resp.status}`);
			hAtt = resp.headers.get('Has-Att');
			return resp.text() }
		)
		.then(data => {
				if (hAtt) dttl.innerHTML += ' '+UNote.I.clip;
				_Id('qviewdata').innerHTML = data;
			}
		)
		.catch(err => alert('Failure: '+err));
	};


	UNote.link2 = (elm) => {
		let nlnk = elm.parentElement.dataset.href===undefined ? elm.dataset.href : elm.parentElement.dataset.href;
		window.location.href = nlnk;
	};


	// close showed layer
	const mclose = () => {
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

	// watch for entry in comment form; enable submit button when properly populated
	UNote.watchcmnt = () => {
		let cmntSbb = _Id('cmntSbb');
		if (document.forms.newcmnt.name.value.trim() && document.forms.newcmnt.cmntext.value.trim()) {
			cmntSbb.disabled = false;
		} else {
			cmntSbb.disabled = true;
		}
	};



	// handle action item clicks
	const evtHandler = (e) => {
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
		// provide focus for some modal dialogs
		let dlg = _Id('foldercr-modal');
		dlg && dlg.addEventListener('shown.bs.modal', () => _Id('jform_title').focus() );
		dlg = _Id('comment-modal');
		dlg && dlg.addEventListener('shown.bs.modal', () => _Id('cmnt-text').focus() );
	});

})(window.UNote = window.UNote || {});

if (typeof Joomla != 'undefined')
	Joomla.submitbutton = function (butt) {
		let bp = butt.split('.');
		if (bp[1] == 'cancel') return true;
		Joomla.submitform(butt);
	};
