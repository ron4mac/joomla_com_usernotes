<?php
/**
 * @package    com_usernotes
 *
 * @copyright  Copyright (C) 2016 RJCreations - All rights reserved.
 * @license    GNU General Public License version 3 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.multiselect');

//var_dump('vdf',$this);jexit();

$listOrder	= $this->state('list.ordering');
$listDirn	= $this->state('list.direction');
$canDo		= UserNotesHelper::getActions();
?>
<form action="<?php echo JRoute::_('index.php?option=com_usernotes&view=usernotes'); ?>" method="post" name="adminForm" id="adminForm">
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
		<table class="table table-striped adminlist">
			<thead>
				<tr>
					<th width="1%"></th>
					<th width="1%"><?php echo JHtml::_('myGrid.checkall'); ?></th>
					<th width="15%">
						<?php echo JHtml::_('grid.sort', 'COM_USERNOTES_USERNAME', 'username', $listDirn, $listOrder); ?>
					</th>
					<th width="15%">
						<?php echo JHtml::_('grid.sort', 'COM_USERNOTES_FULLNAME', 'fullname', $listDirn, $listOrder); ?>
					</th>
					<th width="15%">
						<?php echo JHtml::_('grid.sort', 'COM_USERNOTES_USERID', 'userid', $listDirn, $listOrder); ?>
					</th>
					<th width="30%">
						&#160;
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
				<tr class="row<?php echo $i % 2; ?>">
					<td class="right">
						<?php echo $i + 1 + $this->pagination->limitstart; ?>
					</td>
					<td>
						<?php echo JHtml::_('grid.id', $i, $item['uid']); ?>
					</td>
					<td>
						<?php echo $item['uname']; ?>
						<a href="<?php echo JRoute::_('index.php?option=com_usernotes&view=events&uid=').$item['uid']; ?>">view</a>
					</td>
					<td>
						<?php echo $item['name']; ?>
					</td>
					<td>
						<?php echo $item['uid'] ?>
					</td>
					<td>
						&#160;
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
			<?php echo JHtml::_('form.token'); ?>
		</div>
	</div>
</form>
