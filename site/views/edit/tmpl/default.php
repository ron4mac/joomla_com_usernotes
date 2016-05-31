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
JHtml::_('behavior.keepalive');
JHtml::_('bootstrap.tooltip');
//JHtml::_('behavior.formvalidation');	//before J3.4
JHTML::_('behavior.formvalidator');
$jdoc = JFactory::getDocument();
$jdoc->addScript('components/com_usernotes/static/js/oopim.js');
$jdoc->addScriptDeclaration('var baseURL = "'.JUri::base().'";
var aBaseURL = "'.JUri::base().'index.php?option=com_usernotes&format=raw&unID='.urlencode($this->notesID).'&task=";
var itemID = '.$this->item->itemID.';
var notesID = "'.urlencode($this->notesID).'";
var parentID = '.$this->item->parentID.';
var contentID = '.($this->item->contentID?:0).';
');

$task = $this->type == 'f' ? 'edit.saveFolder' : 'edit.saveNote';
$lgnd = $this->type == 'f' ? '_F' : '';
//var_dump($this->form);
?>
<div class="unote-edit">
	<form action="" method="post" name="adminForm" id="adminForm" class="form-validate">
		<input type="hidden" name="task" value="<?=$task?>" />
		<span class="unote-buttons">
			<input type="reset" value="Reset" class="btn" />
			<button type="button" class="btn" onclick="Joomla.submitbutton('edit.cancelEdit')"><?= JText::_('JCANCEL') ?></button>
		<!--	<button type="button" class="btn btn-primary validate" onclick="Joomla.submitbutton('<?=$task?>')"><?= JText::_('JSAVE') ?></button> -->
			<button type="submit" class="btn btn-primary validate"><?= JText::_('JSAVE') ?></button>
		</span>
		<fieldset class="adminform" style="clear:both">
			<!-- <legend><?=JText::_((is_array($this->item)?'COM_USERNOTES_EDIT_FORM_CREATE':'COM_USERNOTES_EDIT_FORM_EDIT').$lgnd); ?></legend> -->
			<ul class="adminformlist">
	<? foreach ($this->form->getFieldset() as $field) {
		if ($field->fieldname == 'maksec' && $this->item->itemID) continue;
		if ($field->fieldname == 'maksec') {
			echo '<li>'.$field->input.$field->label.'</li>'."\n";
			continue;
			} ?>
				<li><?=$field->label?><?=$field->input?></li>
	<? } ?>
			</ul>
		</fieldset>
		<?php echo JHtml::_('form.token'); ?>
	</form>
	<?php if (isset($this->attached) && $this->attached): ?>
	<div id="attachments">
		<?=JHtml::_('usernotes.att_list',$this->attached,$this->item->contentID,true)?>
	</div>
	<?php endif; ?>
</div>
