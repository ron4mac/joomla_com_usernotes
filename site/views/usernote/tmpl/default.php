<?php
/**
 * @package    com_usernotes
 * @copyright  Copyright (C) 2016-2021 RJCreations - All rights reserved.
 * @license    GNU General Public License version 3 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

HTMLHelper::stylesheet('components/com_usernotes/static/css/usernotes.css', ['version' => 'auto']);
HTMLHelper::_('jquery.framework', false);

$this->jDoc->addScript('components/com_usernotes/static/js/usernotes.js', ['version' => 'auto']);
$this->jDoc->addScript('components/com_usernotes/static/js/upload5d.js', ['version' => 'auto']);
$this->jDoc->addScript('components/com_usernotes/static/js/rating.js', ['version' => 'auto']);
$jslang = [
		'ru_sure' => Text::_('COM_USERNOTES_RU_SURE'),
		'fsz2big' => Text::_('COM_USERNOTES_FSZ2BIG'),
		'fbadtyp' => Text::_('COM_USERNOTES_FBADTYP')
	];
$jsvars = [
//	'aBaseURL' => JUri::base().'index.php?option=com_usernotes&format=raw&unID='.urlencode($this->notesID).'&task=',
//	'aBaseURL' => JUri::base().'index.php?option=com_usernotes&format=raw&task=',
	'aBaseURL' => $this->aUrl('format=raw').'&task=',
	'itemID' => $this->item->itemID,
	'notesID' => urlencode($this->notesID),
	'parentID' => $this->item->parentID,
	'contentID' => ($this->item->contentID?:0)
];
$this->jDoc->addScriptDeclaration('var baseURL = "'.JUri::base().'";
//var aBaseURL = "'.JUri::base().'index.php?option=com_usernotes&format=raw&unID='.urlencode($this->notesID).'&task=";
//var aBaseURL = "'.JUri::base().'index.php?option=com_usernotes&format=raw&task=";
//var itemID = '.$this->item->itemID.';
//var notesID = "'.urlencode($this->notesID).'";
//var parentID = '.$this->item->parentID.';
//var contentID = '.$this->item->contentID.';
//var upldDestURL = "'.JUri::base().'index.php?option=com_usernotes&format=raw&unID='.urlencode($this->notesID).'";
//var upldDestURL = "'.JUri::base().'index.php?option=com_usernotes&format=raw";
var upldDestURL = "'.$this->aUrl('format=raw').'";
var fup_payload = {task:"attach", cID:'.$this->item->contentID.'};
var uploadMaxFilesize = '.$this->maxUploadBytes.';
	UNote.L = '.json_encode($jslang).';
	UNote.V = '.json_encode($jsvars).';
');
//var_dump($this->item);
$itemID = $this->item->itemID;
$prning = ($this->state->get('task', 0) === 'printNote');
//echo'<xmp>';var_dump($prning,$this->state->get('task', 0));echo'</xmp>';
if ($prning) echo '<button type="button" class="btn btn-primary" onclick="window.close();window.history.back();">'.Text::_('COM_USERNOTES_PRNDONE').'</button>';
// if not printing, accommodate targeted breadcrumb module
if (!$prning) echo HTMLHelper::_('content.prepare', '{loadposition usernotes_bc}');

if (RJC_DBUG) echo '<div>'.$this->instance.'</div>';
?>
<div id="container">
	<div id="body">
		<div class="rated"><span id="unrating" class="rating" data-default-rating="<?=$this->rating?>"></span> <span id="numrats">(<?=$this->ratcnt?>)</span></div>
		<h3><?php if ($this->item->secured) echo'<span class="icon-unlock" style="font-size:.8em;opacity:0.5"></span>'; ?><?=$this->item->title?></h3>
		<div id="note"><?=$this->item->serial_content?></div>
	</div>
<?php if (!$prning): ?>
	<div id="attachments">
<?php if ($this->attached): ?>
		<?=HTMLHelper::_('usernotes.att_list',$this->attached,$this->item->contentID)?>
<?php endif; ?>
	</div>
	<div class="footer">
		<?php
			echo HTMLHelper::_('usernotes.prnActIcon',$itemID,Text::_('COM_USERNOTES_PRNNOTE'));
		if ($this->access & ITM_CAN_EDIT) {
			echo HTMLHelper::_('usernotes.edtActIcon',$itemID,Text::_('COM_USERNOTES_EDTNOTE'));
			echo HTMLHelper::_('usernotes.attActIcon',$itemID,Text::_('COM_USERNOTES_ADDATCH'));
			echo HTMLHelper::_('usernotes.movActIcon',$itemID,Text::_('COM_USERNOTES_MOVNOTE'));
			echo HTMLHelper::_('usernotes.toolActIcon',$itemID,Text::_('COM_USERNOTES_SPCTOOL'));
		}
		if ($this->access & ITM_CAN_DELE) {
			echo HTMLHelper::_('usernotes.delActIcon',$itemID,Text::_('COM_USERNOTES_DELNOTE'));
		}
		?>
		&nbsp;<?=$this->footMsg?>
	</div>
<?php endif; ?>
</div>
<?php if ($this->access & ITM_CAN_EDIT) : ?>
<div id="putmenu" class="pum" style="display:none" onmouseover="mcancelclosetime()" onmouseout="mclosetime()">
	<ul id="spum">
		<li><a href="#" onclick="UNote.toolAct(event,'dofrac')" title="<?=Text::_('COM_USERNOTES_DOFRACT');?>"><?=Text::_('COM_USERNOTES_DOFRACTZ');?></a></li>
		<li><a href="#" onclick="UNote.toolAct(event,'unfrac')" title="<?=Text::_('COM_USERNOTES_UNFRACT');?>"><?=Text::_('COM_USERNOTES_UNFRACT');?></a></li>
<?php if ($this->attached): ?>
		<li><a href="#" onclick="UNote.toolAct(event,'delatts')" title="<?=Text::_('COM_USERNOTES_DELAATTS');?>" data-sure="<?=strtolower(Text::_('COM_USERNOTES_DELAATTS'));?>"><?=Text::_('COM_USERNOTES_DEL_ATTS');?></a></li>
<?php endif; ?>
	</ul>
</div>
<div id="filupld" class="uplddlog" style="display:none;">
	<span style="color:#36C;"><?=Text::_('COM_USERNOTES_MAXUPLD');?> <?=UserNotesHelper::formatBytes($this->maxUploadBytes)?></span>
	<input type="file" id="upload_field" name="attm[]" multiple="multiple" />
	<div id="dropArea"><?=Text::_('COM_USERNOTES_DROPFILS');?></div>
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
<script>
<?php if ($prning): ?>
window.print();
(function() {

	var beforePrint = function() {
		console.log("Functionality to run before printing.");
	};

	var afterPrint = function() {
		console.log("Functionality to run after printing");
	//	window.close();
	};

	if (window.matchMedia) {
		var mediaQueryList = window.matchMedia("print");
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
<?php endif; ?>
var rating = document.getElementById('unrating');
var r = new SimpleStarRating(rating);
rating.addEventListener('rate', function(e) {	console.log(e);
	if (e.detail === 0) {
		if (!confirm("Clear rating for this item?")) return;
	}
	UNote.addRating(e.detail, function (newr) {
		var rslt = newr.split(":");
		if (rslt[2]) {
			alert(rslt[2]);
//			r.showDefaultRating();
		} else {
			if (rslt[0] == 0) r.setDefaultRating(0);
			r.setCurrentRating(rslt[0]);
			document.getElementById('numrats').innerHTML = rslt[1];
		}
	});
});
</script>
