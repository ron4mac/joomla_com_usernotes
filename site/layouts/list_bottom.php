<?php
/**
* @package		com_usernotes
* @copyright	Copyright (C) 2015-2023 RJCreations. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
* @since		1.3.4
*/
defined('JPATH_BASE') or die;

// placed at page end to provide quickviews from a notes list
?>
<div id="qview-modal" tabindex="-1" class="joomla-modal modal fade" style="display: none;" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">title-holder</h5>
				<button type="button" class="btn-close novalidate" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body" style="max-height: initial;">
				<div id="qviewdata"></div>
			</div>
		</div>
	</div>
</div>
<script>
	UNote.popRate = () => alert('Please open the full note view before trying to rate.');
	let element = document.getElementById('itemsList');
	element.addEventListener('click', (e) => {
		if (e.target.classList.contains('qview')) {
			UNote.qView(e.target);
		}
		if (e.target.classList.contains('docum')) {
			UNote.link2(e.target);
		}
		if (e.target.classList.contains('link2')) {
			UNote.link2(e.target);
		}
	});
</script>
