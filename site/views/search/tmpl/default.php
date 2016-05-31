<?php
/**
 * @package    com_usernotes
 *
 * @copyright  Copyright (C) 2016 RJCreations - All rights reserved.
 * @license    GNU General Public License version 3 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

JHtml::stylesheet('components/com_usernotes/static/css/oopim.css');
JHtml::_('jquery.framework', false);
$jdoc = JFactory::getDocument();
$jdoc->addScript('components/com_usernotes/static/js/oopim.js');
$jdoc->addScript('components/com_usernotes/static/js/notesview.js');
?>
<div class="search">
	<form name="sqry" onsubmit="return Oopim.performSearch(this,<?=$this->parentID?>)">
		<input type="hidden" name="task" value="search" />
		<input type="search" name="sterm" results="10" autosave="oopim_notes" placeholder="Search..." />
	</form>
</div>
<div id="container" style="margin-top:1em;">
	<div id="body">
	<ul id="itemsList">
	<?php foreach ($this->items as $item): ?>
		<li class="<?=($item->isParent?'note fold':'note docu').($item->shared?'_s':'')?>">
			<?=JHtml::_('usernotes.itemLink', $item);?>
		</li>
	<?php endforeach; ?>
	</ul>
<br />&nbsp;
	</div>
</div>
