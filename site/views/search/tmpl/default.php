<?php
/**
 * @package    com_usernotes
 * @copyright  Copyright (C) 2016-2021 RJCreations - All rights reserved.
 * @license    GNU General Public License version 3 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

JHtml::stylesheet('components/com_usernotes/static/css/oopim.css', ['version' => 'auto']);
JHtml::_('jquery.framework', false);

$this->jDoc->addScript('components/com_usernotes/static/js/usernotes.js', ['version' => 'auto']);

if (RJC_DBUG) echo '<div>'.$this->instance.'</div>';

echo JHtml::_('usernotes.searchField', !empty($this->parentID) ? $this->parentID : 0);
?>
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
