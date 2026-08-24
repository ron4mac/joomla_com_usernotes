/**
* @package		com_usernotes
* @copyright	Copyright (C) 2015-2024 RJCreations. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
* @since		1.4.4
*/
'use strict';

UNote.Upld5d = (function(){

	var isInitted = false;

	var totProgressDiv;
	var progressDiv;
	var totalProgressElem;

	var upQueue = [];
	var maxXfer = 3;
	var inPrg = 0;
	var errCnt = 0;
	var total2do = 0;
	var totalDone = 0;

	var responses = '';

	var filFldNam = 'user_file[]';

	/** @noinline */
	const _Id = (elm) => document.getElementById(elm);

	// utility element creator
	const CreateElement = (type, cont, attr) => {
		let elem = document.createElement(type);
		if (cont) elem.innerHTML = cont;
		for (let key in attr) {
			elem.setAttribute(key, attr[key]);
		}
		return elem;
	};

	// file drag hover
	const FileDragHover = (e) => {
		e.stopPropagation();
		e.preventDefault();
		e.target.className = (e.type == "dragover" ? "hover" : "");
	};

	// file selection
	const FileSelectHandler = (e) => {

		// cancel event and hover styling
		FileDragHover(e);

		// fetch FileList object
		var files = e.target.files || e.dataTransfer.files;

		// process all File objects
		for (var i = 0, f; f = files[i]; i++) {
			total2do += f.size;
			upQueue.push(f);
			NextInQueue(false,'fsel');
		}
	};

	const NextInQueue = (decr,tag) => {
		if (decr) {
			if (! --inPrg) {
				if (typeof(UNote.fup_done == 'function')) UNote.fup_done(errCnt);
				total2do = totalDone = errCnt = 0;
				if (responses) { console.log(responses); }
			}
		}
		if (upQueue.length && (!maxXfer || inPrg < maxXfer)) {
			var ufo = new UploadFileObj(upQueue.shift());
			inPrg++;
		}
	};

	const UpdateTotalProgress = (adsz) => {
		if (!totProgressDiv) return;
		totalDone += adsz;
		let pc = 100 * totalDone / total2do;
		totProgressDiv.style.width = pc + "%";
	};

	// progress bar object
	function ProgressBar (fileObj, sclass) {
		let $ = this;
		
		$.show = (percent) => {
			let p = 100 * percent;
			$.pb.style.width = p + "%";
			if (percent === 1) {
				$.pb.className = 'indeterm';
			}
		};
		$.msg = (msg, err) => {
			$.pbi.innerHTML += '<br />' + msg;
			if (err) {
				$.pbi.className = 'pbfinf failure';
				errCnt++;
			}
		};
		$.rmov = () => {
			$.pbw._ufo = null;
			progressDiv.removeChild($.pbw);
			$.fObj = null;
		};

		// create progress bar
		let pbw = CreateElement('div', '', {class:'pbwrp'});
		$.pb = pbw.appendChild(CreateElement('div', '', {class:sclass}));
		let pbv = fileObj.fn + '<div class="abortX" aria-hidden="true" onclick="this.parentNode.parentNode._ufo.doAbort(true);">'+UNote.I.abrt+'</div>';
		$.pbi = pbw.appendChild(CreateElement('div', pbv, {class:'pbfinf'}));
		progressDiv.appendChild(pbw);
		$.pbw = pbw;
		$.pbw._ufo = fileObj;
		$.fObj = fileObj;
		return $;
	}

	function UploadFileObj (file) {
		let $ = this;
		let errM = null;

		$.fn = file.fileName || file.name;
		$.lastsz = 0;
		$.fsize = file.size;

		$.doAbort = function() {

			if ($.xhr) { $.xhr.abort(); }
			else $.progress.rmov();
		};

		$.xhr = new XMLHttpRequest();
		if ($.xhr.upload) {

			$.xhr._fName = file.name;
			$.xhr.upload.onabort = function(evt) {
				UpdateTotalProgress($.fsize - $.lastsz);
			};

			$.xhr.upload.onloadstart = function(evt) {
				$.xhr.upload.onprogress = function(e) {
					if (!e.lengthComputable) return;
					$.progress.show(e.loaded / e.total);
					UpdateTotalProgress(e.loaded - $.lastsz);
					$.lastsz = e.loaded;
					};
			};

			if (typeof(fup_ftypes) == 'object' && fup_ftypes.indexOf(file.type) < 0) {
				errM = UNote.sprintf(UNote.L.fbadtyp, file.type);
			} else if (file.size > uploadMaxFilesize) {
				errM = UNote.L.fsz2big;
			}

			// create progress bar
			$.progress = new ProgressBar($, 'normpb');
			
			if (errM) {
				$.progress.msg(errM, true);
				UpdateTotalProgress(file.size);
				$.xhr = null;
				NextInQueue(true,'errM');
				return;
			}

			// file received/failed
			$.xhr.onreadystatechange = function(e) {
				if ($.xhr.readyState == 4) {
					// on good result, remove progress bar
					if ($.xhr.status == 200) {
						$.progress.rmov();
						responses+=$.xhr.responseText;
					} else {
						let msg =  ($.xhr.status == 0) ? '-- aborted' : ($.xhr.status + ': ' + ($.xhr.statusText || $.xhr.responseText));
						$.progress.msg(msg, true);
						console.log($.xhr);
					}
					$.xhr = null;
					NextInQueue(true,'rst');
				}
			};

			// start upload
			$.xhr.open("POST", upldDestURL, true);
			//$.xhr.setRequestHeader("HTTP_X_REQUESTED_WITH", "XMLHttpRequest");
			if (fup_payload) {
				var formData = new FormData();
				formData.append(filFldNam, file);
				for (var key in fup_payload) {
					formData.append(key, fup_payload[key]);
				}
				$.xhr.send(formData);
			} else {
				$.xhr.setRequestHeader("Content-Type", "application/octet-stream");
				$.xhr.setRequestHeader("X_FILENAME", file.name);
				$.xhr.send(file);
			}
		}
	}

	return {
		Init: () => {
			let fSel = _Id("upload_field");
			if (isInitted) {
				// clear stuff from previous
				fSel.value = '';
				totProgressDiv.style.width = 0;
				_Id("fprogress").innerHTML = '';
				return;
			}
	//		let fDrag = _Id("dropArea"),
	//			submitbutton = _Id("submitbutton");
	
			// file select
			if (fSel) {
				fSel.addEventListener("change", FileSelectHandler, false);
				filFldNam = fSel.name;
			}
	
			// is XHR2 available?
			var xhr = new XMLHttpRequest();
			if (xhr.upload) {
				let fDrag = _Id("dropArea");
				// file drop
				fDrag.addEventListener("dragover", FileDragHover, false);
				fDrag.addEventListener("dragleave", FileDragHover, false);
				fDrag.addEventListener("drop", FileSelectHandler, false);
				fDrag.style.display = "block";
	
				// remove submit button
	//			if (submitbutton) submitbutton.style.display = "none";
	
				// progress display area
				totProgressDiv = _Id("totprogress");
				progressDiv = _Id("fprogress");
			}
			xhr = null;
			isInitted = true;
		}
	};

})();
