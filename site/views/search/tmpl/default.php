<?php
/**
* @package		com_usernotes
* @copyright	Copyright (C) 2015-2022 RJCreations. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
*/
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;

if (RJC_DBUG) echo '<div class="RJDBG">'.json_encode($this->instanceObj).'</div>';

// accommodate targeted breadcrumb module
echo HTMLHelper::_('content.prepare', '{loadposition usernotes_bc}');
// display the search field
echo HTMLHelper::_('usernotes.searchField', !empty($this->parentID) ? $this->parentID : 0, $this->sterm);
?>
<div id="container" class="searchr">
	<div id="body">
	<div id="itemsList">
	<?php foreach($this->items as $item): ?>
		<div class="item">
			<?=HTMLHelper::_('usernotes.itemLink', $item);?>
		</div>
	<?php endforeach; ?>
	</div>
	</div>
	<div class="footer">&nbsp;</div>
</div>
