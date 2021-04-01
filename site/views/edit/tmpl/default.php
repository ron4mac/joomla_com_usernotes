<?php
/**
 * @package    com_usernotes
 * @copyright  Copyright (C) 2016-2021 RJCreations - All rights reserved.
 * @license    GNU General Public License version 3 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('behavior.formvalidator');

// Build values for javascript use
$jsvars = [
//	'aBaseURL' => JUri::base().'index.php?option=com_usernotes&format=raw&unID='.urlencode($this->notesID).'&task=',
//	'aBaseURL' => JUri::base().'index.php?option=com_usernotes&format=raw&task=',
	'aBaseURL' => $this->aUrl('format=raw').'&task=',
	'itemID' => $this->item->itemID,
	'notesID' => urlencode($this->notesID),
	'parentID' => $this->item->parentID,
	'contentID' => ($this->item->contentID?:0)
];
$jslang = [
		'sure_del_att' => Text::_('COM_USERNOTES_SURE_DEL_ATT'),
		'rename_att' => Text::_('COM_USERNOTES_RENAME_ATT'),
		'ru_sure' => Text::_('COM_USERNOTES_RU_SURE')
	];
$this->jDoc->addScriptDeclaration('var baseURL = "'.JUri::base().'";
//var aBaseURL = "'.JUri::base().'index.php?option=com_usernotes&format=raw&unID='.urlencode($this->notesID).'&task=";
//var aBaseURL = "'.JUri::base().'index.php?option=com_usernotes&format=raw&task=";
//var itemID = '.$this->item->itemID.';
//var notesID = "'.urlencode($this->notesID).'";
//var parentID = '.$this->item->parentID.';
//var contentID = '.($this->item->contentID?:0).';
	UNote.L = '.json_encode($jslang).';
	UNote.V = '.json_encode($jsvars).';
');

if (RJC_DBUG) echo '<div>'.$this->instance.'</div>';

$task = $this->type == 'f' ? 'edit.saveFolder' : 'edit.saveNote';
$lgnd = $this->type == 'f' ? '_F' : '';
// accommodate targeted breadcrumb module
echo HTMLHelper::_('content.prepare', '{loadposition usernotes_bc}');
?>
<div class="unote-edit">
	<form action="<?=$this->aUrl('view=edit')?>" method="post" name="adminForm" id="adminForm" class="form-validate" data-cancel="edit.cancelEdit">
		<span class="unote-buttons">
			<input type="reset" value="Reset" class="btn" />
			<button type="button" class="btn" onclick="Joomla.submitbutton('edit.cancelEdit')"><?= Text::_('JCANCEL') ?></button>
			<button type="submit" class="btn btn-primary validate"><?= Text::_('JSAVE') ?></button>
		</span>
		<fieldset class="adminform" style="clear:both">
			<div class="adminformlist">
	<?php foreach ($this->form->getFieldset() as $field) {
		if ($field->fieldname == 'maksec' && $this->item->itemID) continue;
		if ($field->fieldname == 'maksec') {
			echo '<div>'.$field->input.$field->label.'</div>'."\n";
			continue;
			}
		echo $this->form->renderField($field->fieldname);
		} ?>
			</div>
		</fieldset>
		<input type="hidden" name="task" value="<?=$task?>" />
		<input type="hidden" name="Itemid" value="<?=$this->itemId?>" />
		<?php echo HTMLHelper::_('form.token'); ?>
	</form>
	<?php if (isset($this->attached) && $this->attached): ?>
	<div id="attachments">
		<?=HTMLHelper::_('usernotes.att_list', $this->attached, $this->item->contentID, true)?>
	</div>
	<?php endif; ?>
</div>
