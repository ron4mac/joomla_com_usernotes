/**
* @package		com_usernotes
* @copyright	Copyright (C) 2015-2022 RJCreations. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
*/
'use strict';
/*global UNote*/
/* exported SimpleStarRating */
var SimpleStarRating = (function () {
	function SimpleStarRating (target) {
		function attr (name, d) {
			var a = target.getAttribute(name);
			return (a ? a : d);
		}

		var max = parseInt(attr('data-stars', 5)),
			disabled = typeof target.getAttribute('disabled') != 'undefined',
			defaultRating = parseFloat(attr('data-default-rating', 0)),
			currentRating = -1,
			stars = [];

		target.innerHTML = '';
		target.style.display = 'inline-block';

		for (var s = 0; s < max; s++) {
			var n = document.createElement('span');
			n.className = 'star';
			n.addEventListener('click', starClick);
			if (s > 0)
				stars[s - 1].appendChild(n);
			else
				target.appendChild(n);

			stars.push(n);
		}

		function disable () {
			target.setAttribute('disabled', '');
			disabled = true;
		}
		this.disable = disable;

		function enable () {
			target.removeAttribute('disabled');
			disabled = false;
		}
		this.enable = enable;

		function setCurrentRating (rating) {
			currentRating = rating;
			target.setAttribute('data-rating', currentRating);
			showCurrentRating();
		}
		this.setCurrentRating = setCurrentRating;

		function setDefaultRating (rating) {
			defaultRating = rating;
			target.setAttribute('data-default-rating', defaultRating);
			showDefaultRating();
		}
		this.setDefaultRating = setDefaultRating;

		this.onrate = function (rating) {};

		target.addEventListener('mouseout', function () {
			disabled = target.getAttribute('disabled') !== null;
			if (!disabled)
				showCurrentRating();
		});

		target.addEventListener('mouseover', function () {
			disabled = target.getAttribute('disabled') !== null;
			if (!disabled)
				clearRating();
		});

		showDefaultRating();

		function showRating (r) {
			clearRating();
			for (var i = 0; i < stars.length; i++) {
				if (i >= r)
					break;
				if (i === Math.floor(r) && i !== r)
					stars[i].classList.add('half');
				stars[i].classList.add('active');
			}
		}

		function showCurrentRating () {
			var ratingAttr = parseFloat(attr('data-rating', 0));
			if (ratingAttr) {
				currentRating = ratingAttr;
				showRating(currentRating);
			} else {
				showDefaultRating();
			}
		}

		function showDefaultRating () {
			defaultRating = parseFloat(attr('data-default-rating', 0));
			showRating(defaultRating);
		}

		function clearRating () {
			for (var i = 0; i < stars.length; i++) {
				stars[i].classList.remove('active');
				stars[i].classList.remove('half');
			}
		}

		function starClick (e) {
			if (disabled) return;
//			console.log(e);
			if (this === e.target) {
				var starClicked = stars.indexOf(e.target);
				if (starClicked !== -1) {
					var starRating = starClicked + 1;
//					setCurrentRating(starRating);
					if (typeof this.onrate === 'function')
						this.onrate(currentRating);
					var evt = new CustomEvent('rate', {
						detail: e.metaKey ? 0 : starRating
					});
					disable();
					target.dispatchEvent(evt);
				}
			}
		}
	}

	return SimpleStarRating;
})();

var RangeR = (function () {
	function RangeR (target) {
		function attr (name, d) {
			var a = target.getAttribute(name);
			return (a ? a : d);
		}

		var disabled = false,	//typeof target.getAttribute('disabled') != 'undefined',
			defaultRating = parseFloat(attr('data-default-rating', 0)),
			currentRating = -1;

		const disable = () => { target.firstElementChild.oninput = null; target.firstElementChild.disabled = true; disabled = true; };
		this.disable = disable;

		function setCurrentRating (rating) {
			currentRating = rating;
			target.setAttribute('data-rating', currentRating);
			target.firstElementChild.value = currentRating;
			target.firstElementChild.style.setProperty('--value', currentRating);
	//		showCurrentRating();
		}
		this.setCurrentRating = setCurrentRating;

		function setDefaultRating (rating) {
			defaultRating = rating;
			target.setAttribute('data-default-rating', defaultRating);
			target.firstElementChild.value = defaultRating;
			target.firstElementChild.style.setProperty('--value', defaultRating);
		//	showDefaultRating();
		}
		this.setDefaultRating = setDefaultRating;

		function sendRating (e, rating) {
	//		console.log(target.firstElementChild);
			console.log(target,e);
			disable();
			let evt = new CustomEvent('rate', { detail: e.metaKey ? 0 : rating });
			target.dispatchEvent(evt);
		}
		this.sendRating = sendRating;

		target.addEventListener('touchend', function (e) {
			if (disabled) return false;
	//		console.log(this.firstElementChild.value);
			let starRating = this.firstElementChild.value;
			sendRating(e, starRating);
		//	if (typeof this.onrate === 'function')
		//		this.onrate(currentRating);
		});

		target.style.display = 'inline-block';
		target.innerHTML = `
	<input
		type = "range"
		class = "rrating"
		min = "1"
		max = "5"
		step = "1"
		oninput = "this.style.setProperty('--value',this.value)"
		onchange = "UNote.robj.sendRating(event,this.value);this.style.setProperty('--fill','#66F')"
		style = "--fill: #3AF;--symbol:var(--star);--value:${defaultRating}">`;
	}

	return RangeR;
})();

UNote.hoistRating = (elm) => {
	let isTouch = ( 'ontouchstart' in window ) || ( navigator.maxTouchPoints > 0 ) || ( navigator.msMaxTouchPoints > 0 );
	return isTouch ? new RangeR(elm) : new SimpleStarRating(elm);
};

UNote.popRate = () => {
	let popr = document.getElementById('popRate');
	let rating = popr.querySelector('.rating');
	let r = UNote.hoistRating(rating);
	UNote.robj = r;
	popr.style.display = 'block';
	window.addEventListener('click', ()=>{ popr.style.display = 'none'; }, {once:true,capture:true});
	window.addEventListener('keydown', ()=>{ popr.style.display = 'none'; }, {once:true,capture:true});
};

UNote.rateEvt = (e) => {	//console.log(e);
	if (e.detail === 0) {
		if (!confirm("Clear rating for this item?")) return;
	}
	UNote.addRating(e.detail, function (newr) {
		document.getElementById("popRate").style.display = "none";
		let rap = document.getElementById('ratep');
		rap.onclick = null;
		rap.classList.remove('active');
		if (newr.err) {
			alert(newr.err);
		} else {
			let rbw = rap.querySelector('.strating');
			let p = newr.ravg/5*100;
			rbw.style.width = p+'%';
			document.getElementById("numrats").innerHTML = `(${newr.rcnt})`;
		}
	});
};
