<?php
/**
* @package		com_usernotes
* @copyright	Copyright (C) 2015-2022 RJCreations. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
*/
defined('_JEXEC') or die;

use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('behavior.formvalidator');

// Build values for javascript use
$jsvars = [
	'aBaseURL' => $this->aUrl('format=raw'),
	'itemID' => $this->item->itemID,
	'parentID' => $this->item->parentID,
	'contentID' => ($this->item->contentID?:0)
];
$jslang = [
		'ru_sure' => Text::_('COM_USERNOTES_RU_SURE')
	];
$this->jDoc->addScriptDeclaration('var baseURL = "'.JUri::base().'";
	UNote.L = '.json_encode($jslang).';
	UNote.V = '.json_encode($jsvars).';
');

if (RJC_DBUG) echo '<div>'.json_encode($this->instanceObj).'</div>';

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
		<input type="hidden" name="Itemid" value="<?=$this->instanceObj->menuid?>" />
		<?php echo HTMLHelper::_('form.token'); ?>
	</form>
</div>
<script>
	if (!UNote.V.itemID) document.getElementById('jform_title').focus();
</script>
