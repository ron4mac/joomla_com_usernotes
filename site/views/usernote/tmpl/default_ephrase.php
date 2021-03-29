<?php
/**
 * @package    com_usernotes
 * @copyright  Copyright (C) 2016-2021 RJCreations - All rights reserved.
 * @license    GNU General Public License version 3 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

JHtml::stylesheet('components/com_usernotes/static/css/oopim.css', ['version' => 'auto']);
JHtml::stylesheet('components/com_usernotes/static/css/pumenu.css', ['version' => 'auto']);
JHtml::_('jquery.framework');

$jslang = [
	'ru_sure' => Text::_('COM_USERNOTES_RU_SURE')
];
$jsvars = [
	'aBaseURL' => $this->aUrl('format=raw').'&task=',
//	'itemID' => $this->item->itemID,
//	'notesID' => urlencode($this->notesID),
//	'parentID' => $this->item->parentID,
//	'contentID' => ($this->item->contentID?:0)
];

$this->jDoc->addScript('components/com_usernotes/static/js/usernotes.js', ['version' => 'auto']);
$this->jDoc->addScriptDeclaration('var baseURL = "'.JUri::base().'";
//var aBaseURL = "'.JUri::base().'index.php?option=com_usernotes&format=raw&unID='.urlencode($this->notesID).'&task=";
//var aBaseURL = "'.JUri::base().'index.php?option=com_usernotes&format=raw&task=";
//var itemID = '.$this->item->itemID.';
//var notesID = "'.urlencode($this->notesID).'";
//var parentID = '.$this->item->parentID.';
//var contentID = '.$this->item->contentID.';
	UNote.L = '.json_encode($jslang).';
	UNote.V = '.json_encode($jsvars).';
');
$itemID = $this->item->itemID;
// accommodate targeted breadcrumb module
echo JHtml::_('content.prepare', '{loadposition usernotes_bc}');
?>
<div id="container">
	<div id="body">
		<h3><span class="icon-lock" style="font-size:.8em;opacity:0.5"></span><?=$this->item->title?></h3>
		<div class="ephrase">
			<form action="" method="post" class="form-validate">
				<?php echo $this->form->renderFieldset('ephrase')?>
				<button type="submit" class="btn btn-primary"><?=Text::_('JSUBMIT');?></button>
			</form>
		</div>
	</div>
	<div class="footer">
		<?php
		//	echo JHtml::_('usernotes.prnActIcon', $itemID, Text::_('COM_USERNOTES_PRNNOTE'));
		if ($this->access & ITM_CAN_EDIT) {
			echo JHtml::_('usernotes.movActIcon', $itemID, Text::_('COM_USERNOTES_MOVNOTE'));
		}
		if ($this->access & ITM_CAN_DELE) {
			echo JHtml::_('usernotes.delActIcon', $itemID, Text::_('COM_USERNOTES_DELNOTE'));
		}
		?>
		&nbsp;<?=$this->footMsg?>
	</div>
</div>
