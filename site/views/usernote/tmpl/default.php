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
JHtml::_('jquery.framework', false);
$jdoc = JFactory::getDocument();
$jdoc->addScript('components/com_usernotes/static/js/oopim.js');
$jdoc->addScript('components/com_usernotes/static/js/pumenu.js');
$jdoc->addScript('components/com_usernotes/static/js/upload5d.js');
$jdoc->addScript('components/com_usernotes/static/js/notesview.js');
$jslang = array(
		'ru_sure' => JText::_('COM_USERNOTES_RU_SURE')
	);
$jsvars = array(
	'aBaseURL' => JUri::base().'index.php?option=com_usernotes&format=raw&unID='.urlencode($this->notesID).'&task=',
	'itemID' => $this->item->itemID,
	'notesID' => urlencode($this->notesID),
	'parentID' => $this->item->parentID,
	'contentID' => ($this->item->contentID?:0)
);
$jdoc->addScriptDeclaration('var baseURL = "'.JUri::base().'";
var aBaseURL = "'.JUri::base().'index.php?option=com_usernotes&format=raw&unID='.urlencode($this->notesID).'&task=";
var itemID = '.$this->item->itemID.';
var notesID = "'.urlencode($this->notesID).'";
var parentID = '.$this->item->parentID.';
var contentID = '.$this->item->contentID.';
var upldDestURL = "'.JUri::base().'index.php?option=com_usernotes&format=raw&unID='.urlencode($this->notesID).'";
var fup_payload = {task:"attach", cID:'.$this->item->contentID.'};
var uploadMaxFilesize = '.$this->maxUploadBytes.';
	Oopim.L = '.json_encode($jslang).';
	Oopim.V = '.json_encode($jsvars).';
');
//var_dump($this->item);
$itemID = $this->item->itemID;
$prning = ($this->state->get('task', 0) === 'printNote');
//echo'<xmp>';var_dump($prning,$this->state->get('task', 0));echo'</xmp>';
if ($prning) echo '<button type="button" class="btn btn-primary" onclick="window.close();window.history.back();">'.JText::_('COM_USERNORES_PRNDONE').'</button>';
// if not printing, accommodate targeted breadcrumb module
if (!$prning) echo JHtml::_('content.prepare', '{loadposition usernotes_bc}');
?>
<div id="container">
	<div id="body">
		<h3><?php if ($this->item->secured) echo'<span class="icon-unlock" style="font-size:.8em;opacity:0.5"></span>'; ?><?=$this->item->title?></h3>
		<div id="note"><?=$this->item->serial_content?></div>
	</div>
<?php if (!$prning): ?>
	<div id="attachments">
<?php if ($this->attached): ?>
		<?=JHtml::_('usernotes.att_list',$this->attached,$this->item->contentID)?>
<?php endif; ?>
	</div>
	<div class="footer">
		<?php
			echo JHtml::_('usernotes.prnActIcon',$itemID,JText::_('COM_USERNOTES_PRNNOTE'));
		if ($this->access & ITM_CAN_EDIT) {
			echo JHtml::_('usernotes.edtActIcon',$itemID,JText::_('COM_USERNOTES_EDTNOTE'));
			echo JHtml::_('usernotes.attActIcon',$itemID,JText::_('COM_USERNOTES_ADDATCH'));
			echo JHtml::_('usernotes.movActIcon',$itemID,JText::_('COM_USERNOTES_MOVNOTE'));
			echo JHtml::_('usernotes.toolActIcon',$itemID,JText::_('COM_USERNOTES_SPCTOOL'));
		}
		if ($this->access & ITM_CAN_DELE) {
			echo JHtml::_('usernotes.delActIcon',$itemID,JText::_('COM_USERNOTES_DELNOTE'));
		}
		?>
		&nbsp;<?=$this->footMsg?>
	</div>
<?php endif; ?>
</div>
<?php if ($this->access & ITM_CAN_EDIT) : ?>
<div id="putmenu" class="pum" style="display:none" onmouseover="mcancelclosetime()" onmouseout="mclosetime()">
	<ul id="spum">
		<li><a href="#" onclick="Oopim.toolAct(event,'dofrac')" title="<?=JText::_('COM_USERNOTES_DOFRACT');?>"><?=JText::_('COM_USERNOTES_DOFRACTZ');?></a></li>
		<li><a href="#" onclick="Oopim.toolAct(event,'unfrac')" title="<?=JText::_('COM_USERNOTES_UNFRACT');?>"><?=JText::_('COM_USERNOTES_UNFRACT');?></a></li>
<?php if ($this->attached): ?>
		<li><a href="#" onclick="Oopim.toolAct(event,'delatts')" title="<?=JText::_('COM_USERNOTES_DELAATTS');?>" data-sure="<?=strtolower(JText::_('COM_USERNOTES_DELAATTS'));?>"><?=JText::_('COM_USERNOTES_DEL_ATTS');?></a></li>
<?php endif; ?>
	</ul>
</div>
<div id="filupld" class="uplddlog" style="display:none;">
	<span style="color:#36C;"><?=JText::_('COM_USERNOTES_MAXUPLD');?> <?=UserNotesHelper::formatBytes($this->maxUploadBytes)?></span>
	<input type="file" id="upload_field" name="attm[]" multiple="multiple" />
	<div id="dropArea"><?=JText::_('COM_USERNOTES_DROPFILS');?></div>
	<div id="result"></div>
	<div id="totprogress"></div>
	<div id="fprogress"></div>
	<hr />
	<button onclick="this.parentNode.style.display='none'">Close</button>
</div>
<?php endif; ?>
<?php if ($this->attached): ?>
<iframe id="dnldf" style="display:none;"></iframe>
<?php endif; ?>
<?php
if ($prning) {
	echo '<script>
window.print();
(function() {

	var beforePrint = function() {
		console.log(\'Functionality to run before printing.\');
	};

	var afterPrint = function() {
		console.log(\'Functionality to run after printing\');
	//	window.close();
	};

	if (window.matchMedia) {
		var mediaQueryList = window.matchMedia(\'print\');
		mediaQueryList.addListener(function(mql) {
			if (mql.matches) {
				beforePrint();
			} else {
				afterPrint();
			}
		});
	}

	window.onbeforeprint = beforePrint;
	window.onafterprint = afterPrint;

}());
</script>
';
}
?>
