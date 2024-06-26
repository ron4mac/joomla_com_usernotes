'use strict';
/*
 * This content is licensed according to the W3C Software License at
 * https://www.w3.org/Consortium/Legal/2015/copyright-software-and-document
 *
 * File: slider-rating.js
 *
 * Desc: RatingSlider widget that implements ARIA Authoring Practices
 */

UNote.popRate = () => {
	let popr = document.getElementById('id-rating');
	popr.style.display = 'block';
};

UNote.cancelRate = (elm) => {
	elm.style.display = 'none';
};

UNote.rateEvt = (e, clr=false) => {	console.log(e, clr);
	let val = e.target ? e.target.ariaValueNow : e.ariaValueNow;
	if (clr && val == 0) {
		if (!confirm("Clear rating for this item?")) return;
	}
	UNote.addRating(val, function (newr) {
		document.getElementById("id-rating").style.display = "none";
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

class RatingSlider {
	constructor (domNode) {
		this.sliderNode = domNode;

		this.isMoving = false;

		this.svgNode = domNode.querySelector('svg');

		// Inherit system text colors
		//	let  color = getComputedStyle(this.sliderNode).color;
		//	this.svgNode.setAttribute('color', color);

		this.starsWidth = 198;
		this.starsX = 0;

		this.svgPoint = this.svgNode.createSVGPoint();

		// define possible slider positions

		this.sliderNode.addEventListener(
			'keydown',
			this.onSliderKeydown.bind(this)
		);

		this.svgNode.addEventListener('click', this.onRailClick.bind(this));
		this.svgNode.addEventListener(
			'pointerdown',
			this.onSliderPointerDown.bind(this)
		);
		document.addEventListener(
			'pointerdown',
			this.onDocPointerDown.bind(this)
		);

		// bind a pointermove event handler to move pointer
		this.svgNode.addEventListener('pointermove', this.onPointerMove.bind(this));

		// bind a pointerup event handler to stop tracking pointer movements
		domNode.addEventListener('pointerup', this.onPointerUp.bind(this));

		this.addTotalStarsToRatingLabel();
		this.sliderNode.addEventListener(
			'blur',
			this.addTotalStarsToRatingLabel.bind(this)
		);
  }


	// Get point in global SVG space
	getSVGPoint (event) {
		this.svgPoint.x = event.clientX;
		this.svgPoint.y = event.clientY;
		return this.svgPoint.matrixTransform(this.svgNode.getScreenCTM().inverse());
	}


	getValue () {
		return parseFloat(this.sliderNode.getAttribute('aria-valuenow'));
	}


	getValueMin () {
		return parseFloat(this.sliderNode.getAttribute('aria-valuemin'));
	}


	getValueMax () {
		return parseFloat(this.sliderNode.getAttribute('aria-valuemax'));
	}


	isInRange (value) {
		let valueMin = this.getValueMin(),
			valueMax = this.getValueMax();
		return value <= valueMax && value >= valueMin;
	}


	getValueText (value) {
		switch (value) {
			case 0:
				return 'zero stars';

			case 0.5:
				return 'one half star';

			case 1.0:
				return 'one star';

			case 1.5:
				return 'one and a half stars';

			case 2.0:
				return 'two stars';

			case 2.5:
				return 'two and a half stars';

			case 3.0:
				return 'three stars';

			case 3.5:
				return 'three and a half stars';

			case 4.0:
				return 'four stars';

			case 4.5:
				return 'four and a half stars';

			case 5.0:
				return 'five stars';

			default:
				break;
		}

		return 'Unexpected value: ' + value;
	}


	getValueTextWithMax (value) {
		switch (value) {
			case 0:
				return 'zero of five stars';

			case 0.5:
				return 'one half of five stars';

			case 1.0:
				return 'one of five stars';

			case 1.5:
				return 'one and a half of five stars';

			case 2.0:
				return 'two of five stars';

			case 2.5:
				return 'two and a half of five stars';

			case 3.0:
				return 'three of five stars';

			case 3.5:
				return 'three and a half of five stars';

			case 4.0:
				return 'four of five stars';

			case 4.5:
				return 'four and a half of five stars';

			case 5.0:
				return 'five of five stars';

			default:
				break;
		}

		return 'Unexpected value: ' + value;
	}


	moveSliderTo (value) {
		let valueMax, valueMin;

		valueMin = this.getValueMin();
		valueMax = this.getValueMax();

		value = Math.min(Math.max(value, valueMin), valueMax);

		this.sliderNode.setAttribute('aria-valuenow', value);

		this.sliderNode.setAttribute('aria-valuetext', this.getValueText(value));
	}


	onSliderKeydown (event) {
		let flag = false,
			value = this.getValue(),
			valueMin = this.getValueMin(),
			valueMax = this.getValueMax();

		switch (event.key) {
			case 'ArrowLeft':
			case 'ArrowDown':
				this.moveSliderTo(value - 0.5);
				flag = true;
				break;

			case 'ArrowRight':
			case 'ArrowUp':
				this.moveSliderTo(value + 0.5);
				flag = true;
				break;

			case 'PageDown':
				this.moveSliderTo(value - 1);
				flag = true;
				break;

			case 'PageUp':
				this.moveSliderTo(value + 1);
				flag = true;
				break;

			case 'Home':
				this.moveSliderTo(valueMin);
				flag = true;
				break;

			case 'End':
				this.moveSliderTo(valueMax);
				flag = true;
				break;

			default:
				break;
		}

		if (flag) {
			event.preventDefault();
			event.stopPropagation();
		}
	}


	addTotalStarsToRatingLabel () {
		let valuetext = this.getValueTextWithMax(this.getValue());
		this.sliderNode.setAttribute('aria-valuetext', valuetext);
	}


	onRailClick (event) {
		let x = this.getSVGPoint(event).x,
			min = this.getValueMin(),
			max = this.getValueMax(),
			diffX = x - this.starsX,
			value = Math.round((2 * (diffX * (max - min))) / this.starsWidth) / 2;

		this.moveSliderTo(value);

		event.preventDefault();
		event.stopPropagation();

		// Set focus to the clicked handle
		this.sliderNode.focus();
	}


	onSliderPointerDown (event) {
		this.isMoving = true;

		event.preventDefault();
		event.stopPropagation();

		// Set focus to the clicked handle
		this.sliderNode.focus();
	}


	onDocPointerDown (event) {
		// Remove/cancel the rating slider
		UNote.cancelRate(this.sliderNode);
	}


	onPointerMove (event) {
		if (this.isMoving) {
			let x = this.getSVGPoint(event).x,
				min = this.getValueMin(),
				max = this.getValueMax(),
				diffX = x - this.starsX,
				value = Math.round((2 * (diffX * (max - min))) / this.starsWidth) / 2;

			this.moveSliderTo(value);

			event.preventDefault();
			event.stopPropagation();
		}
	}


	onPointerUp (event) {	console.log(event);
		this.isMoving = false;
		UNote.rateEvt(this.sliderNode, event.altKey);
	}
}


// Initialize RatingSliders on the page
window.addEventListener('load', function () {
	let sliders = document.querySelectorAll('.rating-slider');
	for (let i = 0; i < sliders.length; i++) {
		new RatingSlider(sliders[i]);
	}
});
