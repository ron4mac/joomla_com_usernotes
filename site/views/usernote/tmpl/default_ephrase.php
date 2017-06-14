<?php
/**
 * @package    com_usernotes
 *
 * @copyright  Copyright (C) 2016 RJCreations - All rights reserved.
 * @license    GNU General Public License version 3 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

JHtml::stylesheet('components/com_usernotes/static/css/oopim.css');
JHtml::stylesheet('components/com_usernotes/static/css/pumenu.css');
JHtml::_('jquery.framework');
$jdoc = JFactory::getDocument();
$jdoc->addScriptDeclaration('var baseURL = "'.JUri::base().'";
var aBaseURL = "'.JUri::base().'index.php?option=com_usernotes&format=raw&unID='.urlencode($this->notesID).'&task=";
var itemID = '.$this->item->itemID.';
var notesID = "'.urlencode($this->notesID).'";
var parentID = '.$this->item->parentID.';
var contentID = '.$this->item->contentID.';
');
$jdoc->addScript('components/com_usernotes/static/js/oopim.js');
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
				<button type="submit" class="btn btn-primary"><?=JText::_('JSUBMIT');?></button>
			</form>
		</div>
	</div>
	<div class="footer">
		<?php
			echo JHtml::_('usernotes.prnActIcon',$itemID,'Print note');
		if ($this->access & ITM_CAN_EDIT) {
			echo JHtml::_('usernotes.movActIcon',$itemID,'Move note');
		}
		if ($this->access & ITM_CAN_DELE) {
			echo JHtml::_('usernotes.delActIcon',$itemID,'Delete note');
		}
		?>
		&nbsp;<?=$this->footMsg?>
	</div>
</div>
