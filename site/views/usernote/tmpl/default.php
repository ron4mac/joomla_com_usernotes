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
$jdoc->addScriptDeclaration('var baseURL = "'.JUri::base().'";
var aBaseURL = "'.JUri::base().'index.php?option=com_usernotes&format=raw&unID='.urlencode($this->notesID).'&task=";
var itemID = '.$this->item->itemID.';
var notesID = "'.urlencode($this->notesID).'";
var parentID = '.$this->item->parentID.';
var contentID = '.$this->item->contentID.';
var upldDestURL = "'.JUri::base().'index.php?option=com_usernotes&format=raw&unID='.urlencode($this->notesID).'";
var fup_payload = {task:"attach", cID:'.$this->item->contentID.'};
var uploadMaxFilesize = '.$this->maxUploadBytes.';
');
$jdoc->addScript('components/com_usernotes/static/js/oopim.js');
$jdoc->addScript('components/com_usernotes/static/js/pumenu.js');
$jdoc->addScript('components/com_usernotes/static/js/upload5d.js');
$jdoc->addScript('components/com_usernotes/static/js/notesview.js');
//var_dump($this->item);
$itemID = $this->item->itemID;
$prning = ($this->state->get('task', 0) === 'printNote');
//echo'<xmp>';var_dump($prning,$this->state->get('task', 0));echo'</xmp>';
if ($prning) echo '<button type="button" class="btn btn-primary" onclick="window.close();window.history.back();">'.JText::_('Done with Printing.').'</button>';
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
			echo JHtml::_('usernotes.prnActIcon',$itemID,'Print note');
		if ($this->access & ITM_CAN_EDIT) {
			echo JHtml::_('usernotes.edtActIcon',$itemID,'Edit note');
			echo JHtml::_('usernotes.attActIcon',$itemID,'Add attachment');
			echo JHtml::_('usernotes.movActIcon',$itemID,'Move note');
			echo JHtml::_('usernotes.toolActIcon',$itemID,'Special tools');
		}
		if ($this->access & ITM_CAN_DELE) {
			echo JHtml::_('usernotes.delActIcon',$itemID,'Delete note');
		}
		?>
		&nbsp;<?=$this->footMsg?>
	</div>
<?php endif; ?>
</div>
<?php if ($this->access & ITM_CAN_EDIT) : ?>
<div id="putmenu" class="pum" style="display:none" onmouseover="mcancelclosetime()" onmouseout="mclosetime()">
	<ul id="spum">
		<li><a href="#" onclick="toolAct(event,'dofrac')" title="Make HTML fractions from text">Fractionize</a></li>
		<li><a href="#" onclick="toolAct(event,'unfrac')" title="Return HTML fractions to text">un-Fractionize</a></li>
<?php if ($this->attached): ?>
		<li><a href="#" onclick="toolAct(event,'delatts')" title="Delete all attachments" data-sure="delete all attachments"><?=JText::_('Delete attachments');?></a></li>
<?php endif; ?>
	</ul>
</div>
<div id="filupld" class="uplddlog" style="display:none;">
	<span style="color:#36C;">Max file upload size: <?=UserNotesHelper::formatBytes($this->maxUploadBytes)?></span>
	<input type="file" id="upload_field" name="attm[]" multiple="multiple" />
	<div id="dropArea">Or drop files here</div>
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
