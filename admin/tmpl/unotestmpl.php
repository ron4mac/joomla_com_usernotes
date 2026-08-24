<?php
/**
* @package		com_usernotes
* @copyright	Copyright (C) 2015-2026 RJCreations. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
* @since		1.5.6
*/
defined('_JEXEC') or die;

use Joomla\CMS\Table\Table;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Component\ComponentHelper;
use RJCreations\Component\Usernotes\Administrator\Helper\UsernotesHelper;

$wa = $this->getDocument()->getWebAssetManager();
$wa->useStyle('com_usernotes.css.admin')
	->useScript('multiselect');

// Include the component HTML helpers.
//HTMLHelper::addIncludePath(JPATH_COMPONENT.'/helpers/html');

$listOrder	= $this->state('list.ordering');
$listDirn	= $this->state('list.direction');
$canDo		= UsernotesHelper::getActions();

$component = ComponentHelper::getComponent('com_usernotes');
$extension = Table::getInstance('extension');
$extension->load($component->id);
$manifest = new \Joomla\Registry\Registry($extension->manifest_cache);

echo $manifest->get('version');
?>
<form action="<?php echo Route::_('index.php?option=com_usernotes&view='.$this->relm.'notes'); ?>" method="post" name="adminForm" id="adminForm">
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
		<table class="table table-striped adminlist">
			<thead>
				<tr>
					<th width="1%"></th>
					<th width="1%"><?php echo HTMLHelper::_('myGrid.checkall'); ?></th>
					<th width="15%">
						<?php echo HTMLHelper::_('grid.sort', $relmtext[0], 'username', $listDirn, $listOrder); ?>
					</th>
<?php if($relmtext[1]): ?>
					<th width="15%">
						<?php echo HTMLHelper::_('grid.sort', $relmtext[1], 'fullname', $listDirn, $listOrder); ?>
					</th>
<?php endif; ?>
					<th width="15%">
						<?php echo HTMLHelper::_('grid.sort', $relmtext[2], 'userid', $listDirn, $listOrder); ?>
					</th>
					<th width="30%">
						<span class="d-none d-md-inline"><?php echo Text::_('COM_USERNOTES_INFO'); ?></span>
					</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<td colspan="6">
						<?php echo $this->pagination->getListFooter(); ?>
					</td>
				</tr>
			</tfoot>
			<tbody>
			<?php foreach ($this->items as $i => $item) : ?>
				<tr class="row<?php echo $i % 2; ?> UN-item">
					<td class="right">
						<?php echo $i + 1 + $this->pagination->limitstart; ?>
					</td>
					<td>
						<?php echo HTMLHelper::_('grid.id', $i, $item['uid']); ?>
					</td>
					<td>
						<?php echo $item['uname']; ?>
						<a href="<?php echo Route::_('index.php?option=com_usernotes&view=usernotes&uid=').$item['uid']; ?>">view</a>
					</td>
<?php if($relmtext[1]): ?>
					<td>
						<?php echo $item['name']; ?>
					</td>
<?php endif; ?>
					<td>
						<?php echo substr($item['uid'], 1) ?>
					</td>
					<td>
						<?php
						echo HTMLHelper::_('myGrid.info', $item['info']);
						foreach ($item['msgs'] as $msg) {
							echo '<div class="errm">'.$msg.'</div>';
						}
						?>
					</td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
		<div>
			<input type="hidden" name="task" value="" />
			<input type="hidden" name="boxchecked" value="0" />
			<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
			<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
			<?php echo HTMLHelper::_('form.token'); ?>
		</div>
	</div>
</form>
