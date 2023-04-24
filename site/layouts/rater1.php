<?php
/**
* @package		com_usernotes
* @copyright	Copyright (C) 2015-2023 RJCreations. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
*/
defined('JPATH_BASE') or die;
?>



<style>
:rating-slider {
	--starclr: blue;
}
.rating-slider label {
	display: block;
}

.rating-slider {
	color: #005a9c;
	--starclr: blue;
}

.rating-slider svg {
	forced-color-adjust: auto;
	touch-action: pan-y;
	fill: var(--starclr);
}

.rating-slider svg .focus-ring {
	fill: #eee;
	stroke-width: 0;
	fill-opacity: 0;
}

.rating-slider svg .star {
	stroke-width: 2px;
	stroke: currentcolor;
	fill-opacity: 0;
}

.rating-slider svg .fill-left,
.rating-slider svg .fill-right {
	stroke-width: 0;
	fill-opacity: 0;
}

.rating-slider[aria-valuenow="5"] svg .star {
	fill-opacity: 1;
}
.rating-slider[aria-valuenow="0.5"] svg .star-1 .fill-left {
	fill-opacity: 1;
}
.rating-slider[aria-valuenow="1"] svg .star-1 .star {
	fill-opacity: 1;
}
.rating-slider[aria-valuenow="1.5"] svg .star-1 .star,
.rating-slider[aria-valuenow="1.5"] svg .star-2 .fill-left {
	fill-opacity: 1;
}
.rating-slider[aria-valuenow="2"] svg .star-2 .star {
	fill-opacity: 1;
}
.rating-slider[aria-valuenow="2.5"] svg .star-2 .star,
.rating-slider[aria-valuenow="2.5"] svg .star-3 .fill-left {
	fill-opacity: 1;
}
.rating-slider[aria-valuenow="3"] svg .star-3 .star {
	fill-opacity: 1;
}
.rating-slider[aria-valuenow="3.5"] svg .star-3 .star,
.rating-slider[aria-valuenow="3.5"] svg .star-4 .fill-left {
	fill-opacity: 1;
}
.rating-slider[aria-valuenow="4"] svg .star-4 .star {
	fill-opacity: 1;
}
.rating-slider[aria-valuenow="4.5"] svg .star-4 .star,
.rating-slider[aria-valuenow="4.5"] svg .star-5 .fill-left {
	fill-opacity: 1;
}

/* focus styling */

.rating-slider:focus {
	outline: none;
}

.rating-slider:focus svg .focus-ring {
	stroke-width: 2px;
	stroke: currentcolor;
}

#id-rating {
	display: none;
	position: absolute;
	top: 1rem;
	background-color: lightgrey;
	border: 1px solid grey;
	border-radius: 4px;
	left: 50%;
	transform: translateX(-50%);
}
</style>

<div id="id-rating" class="rating-slider" role="slider" tabindex="0" onblur="UNote.cancelRate(this)" aria-valuemin="0" aria-valuenow="0" aria-valuemax="5" aria-valuetext="no stars" aria-labelledby="id-rating-label">
	<svg width="216" height="48" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
		<defs>
			<g id="star"><polygon points="2.0,13.4 11.7,20.5 8.0,31.1 17.7,24.8 27.4,31.9 23.7,20.5 33.4,13.4 21.4,13.4 17.7,2.0 14.0,13.4"></polygon></g>
			<g id="fill-left"><polygon points="2.0,13.4 11.7,20.5 8.0,31.1 17.7,24.8 17.7,2.0 14.0,13.4"></polygon></g>
		</defs>
		<rect class="focus-ring" x="2" y="2" width="212" height="44" rx="5"></rect>
	<g class="star-1 star-2 star-3 star-4 star-5">
		<use class="star"
			xlink:href="#star"
			x="10"
			y="7"></use>
		<use class="fill-left"
			xlink:href="#fill-left"
			x="10"
			y="7"></use>
	</g>
	<g class="star-2 star-3 star-4 star-5">
		<use class="star"
			xlink:href="#star"
			x="50"
			y="7"></use>
		<use class="fill-left"
			xlink:href="#fill-left"
			x="50"
			y="7"></use>
	</g>
	<g class="star-3 star-4 star-5">
		<use class="star"
			xlink:href="#star"
			x="90"
			y="7"></use>
		<use class="fill-left"
			xlink:href="#fill-left"
			x="90"
			y="7"></use>
	</g>
	<g class="star-4 star-5">
		<use class="star"
			xlink:href="#star"
			x="130"
			y="7"></use>
		<use class="fill-left"
			xlink:href="#fill-left"
			x="130"
			y="7"></use>
	</g>
	<g class="star-5">
		<use class="star"
			xlink:href="#star"
			x="170"
			y="7"></use>
		<use class="fill-left"
			xlink:href="#fill-left"
			x="170"
			y="7"></use>
	</g>
	</svg>
</div>

