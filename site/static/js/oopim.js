Oopim = {};	// a namespace for utility objects

(function($) {

	Oopim.performSearch = function(aform) {
		var sterm = $.trim(aform.sterm.value);
		if (sterm==='') {
			alert('Please provide a search term');
			return false;
		}
		aform.submit();
		return false;
	};

	//Oopim.updateAlist = function (cid) {
	//	$("#attachments").load(entityURL+"/attlist",{ contentID: cid });
	//};

	Oopim.aj_detach = function (cid,fn) {
		if (!confirm("Are you sure you want to delete this attachment?")) return;
		$.post(aBaseURL+"detach", { contentID: cid, file: fn },
			function (data,status,xhr) {
				//console.log(xhr);
				if (data) { alert(data); }
				else { $("#attachments").load(aBaseURL+"attlist&inedit=1",{ contentID: cid }); }
			}
		);
	};

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
					if ($(this).hasClass('sure') && !confirm('Are you sure that you want to '+$(this).attr('data-suremsg')+'?')) return false;
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
