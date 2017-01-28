Oopim = {};	// a namespace for utility objects

(function($) {

	Oopim.performSearch = function(aform) {
		var sterm = $.trim(aform.sterm.value);
		if (sterm==='') {
			alert(this.L.no_sterm);
			return false;
		}
		aform.submit();
		return false;
	};

	Oopim.aj_detach = function (cid,fn) {
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

	Oopim.sprintf = function (format) {
		for (var i = 1; i < arguments.length; i++) {
			format = format.replace( /%s/, arguments[i] );
		}
		return format;
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
					if ($(this).hasClass('sure') && !confirm(Oopim.sprintf(Oopim.L.ru_sure, $(this).attr('data-suremsg')))) return false;
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
