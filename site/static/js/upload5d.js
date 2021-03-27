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

	// getElementById
	function $id(id) {
		return document.getElementById(id);
	}

	// file drag hover
	function FileDragHover(e) {
		e.stopPropagation();
		e.preventDefault();
		e.target.className = (e.type == "dragover" ? "hover" : "");
	}

	// file selection
	function FileSelectHandler(e) {

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
	}

	function NextInQueue(decr,tag) {
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
	}

	function UpdateTotalProgress(adsz) {
		if (!totProgressDiv) return;
		totalDone += adsz;
		var pc = Math.max(parseInt(100 - (totalDone / total2do * 100)), 0);
		totProgressDiv.style.backgroundPosition = pc + "% 0";
	}

	function UploadFileObj (file) {
		var self = this;
		var errM = null;

		this.lastsz = 0;
		this.fsize = file.size;

		this.doAbort = function() {
			if (this.xhr) { this.xhr.abort(); }
			else progressDiv.removeChild(this.progress);
		};

		this.xhr = new XMLHttpRequest();
		if (this.xhr.upload) {

			this.xhr._fName = file.name;
			this.xhr.upload.onabort = function(evt) {
				UpdateTotalProgress(self.fsize - self.lastsz);
			};

			this.xhr.upload.onloadstart = function(evt) {
				this.onprogress = function(e) {
					if (!e.lengthComputable) return;
					var pc = parseInt(100 - (e.loaded / e.total * 100));
					self.progress.style.backgroundPosition = pc + "% 0";
					UpdateTotalProgress(e.loaded - self.lastsz);
					self.lastsz = e.loaded;
					};
			};

			if (typeof(fup_ftypes) == 'object' && fup_ftypes.indexOf(file.type) < 0) {
				errM = UNote.sprintf(UNote.L.fbadtyp, file.type);
			} else if (file.size > uploadMaxFilesize) {
				errM = UNote.L.fsz2big;
			}

			// create progress bar
			this.progress = progressDiv.appendChild(document.createElement("p"));
			this.progress.appendChild(document.createTextNode(file.name));
			this.progress.innerHTML = this.progress.innerHTML + '<img src="'+baseURL+'components/com_usernotes/static/imgs/redX.png" class="abortX" onclick="AbortUpload(this)" />';
			this.progress._upld = this;

			if (errM) {
				this.progress.innerHTML = this.progress.innerHTML + '<br />' +errM;
				this.progress.className = "failure";
				errCnt++;
				UpdateTotalProgress(file.size);
				NextInQueue(true,'errM');
				return;
			}

			// file received/failed
			this.xhr.onreadystatechange = function(e) {
				if (self.xhr.readyState == 4) {
					//self.progress.className = (self.xhr.status == 200 ? "success" : "failure");
					// on good result, remove progress bar
					if (self.xhr.status == 200) {
						self.progress.className = "success";
						progressDiv.removeChild(self.progress);
						responses+=self.xhr.responseText;
					} else {
						errCnt++;
						self.progress.className = "failure";
						if (self.xhr.status == 0) self.progress.innerHTML = self.progress.innerHTML + '<br />-- aborted'
						else self.progress.innerHTML = self.progress.innerHTML + '<br />' + self.xhr.status + ': ' + self.xhr.statusText;
						console.log(self.xhr);
					}
					self.xhr = null;
					NextInQueue(true,'rst');
				}
			};

			// start upload
			this.xhr.open("POST", upldDestURL, true);
			//this.xhr.setRequestHeader("HTTP_X_REQUESTED_WITH", "XMLHttpRequest");
			if (fup_payload) {
				var formData = new FormData();
				formData.append(filFldNam, file);
				for (var key in fup_payload) {
					formData.append(key, fup_payload[key]);
				}
				this.xhr.send(formData);
			} else {
				this.xhr.setRequestHeader("Content-Type", "application/octet-stream");
				this.xhr.setRequestHeader("X_FILENAME", file.name);
				this.xhr.send(file);
			}
		}
	}

	return {
		Init: function () {
			if (isInitted) return;
			var fileselect = $id("upload_field"),
				filedrag = $id("dropArea"),
				submitbutton = $id("submitbutton");
	
			// file select
			if (fileselect) {
				fileselect.addEventListener("change", FileSelectHandler, false);
				filFldNam = fileselect.name;
			}
	
			// is XHR2 available?
			var xhr = new XMLHttpRequest();
			if (xhr.upload) {
	
				// file drop
				filedrag.addEventListener("dragover", FileDragHover, false);
				filedrag.addEventListener("dragleave", FileDragHover, false);
				filedrag.addEventListener("drop", FileSelectHandler, false);
				filedrag.style.display = "block";
	
				// remove submit button
				if (submitbutton) submitbutton.style.display = "none";
	
				// progress display area
				totProgressDiv = $id("totprogress");
				progressDiv = $id("fprogress");
				//totalProgressElem = progressDiv.appendChild(document.createElement("p"));
				//totalProgressElem.appendChild(document.createTextNode('- total progress -'));
				//totalProgressElem.style.textAlign = "center";
			}
			xhr = null;
			isInitted = true;
		}
	};

})();

function AbortUpload (node) {
	node.parentNode._upld.doAbort();
}
