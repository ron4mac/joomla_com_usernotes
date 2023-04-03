<?php
/**
* @package		com_usernotes
* @copyright	Copyright (C) 2015-2023 RJCreations. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
*/
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\HTML\Helpers\Bootstrap;

if (RJC_DBUG) echo '<div class="RJDBG">'.json_encode($this->instanceObj).'</div>';

// accommodate targeted breadcrumb module
echo HTMLHelper::_('content.prepare', '{loadposition usernotes_bc}');
// display the search field
echo HTMLHelper::_('usernotes.searchField', !empty($this->parentID) ? $this->parentID : 0, $this->sterm);

Bootstrap::modal('#qview-modal');
?>
<style>
	.rated, #ratep { float: right; }
</style>
<div id="container" class="searchr">
	<div id="body">
	<div id="itemsList">
	<?php
	foreach($this->items as $item) {
		$xtra = $item->secured ? '' : ' <button class="qview"></button>';
		echo '<div class="item">'. HTMLHelper::_('usernotes.itemLink', $item).$xtra.'</div>';
	}
	?>
	</div>
	</div>
	<div class="footer">&nbsp;</div>
</div>
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
	let element = document.getElementById('itemsList');
	element.addEventListener('click', (e) => {
		if (e.target.classList.contains('qview')) {
			UNote.qView(e.target);
		}
	});
</script>
<?php
