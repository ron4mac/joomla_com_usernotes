<?php
/**
* @package		com_usernotes
* @copyright	Copyright (C) 2015-2024 RJCreations. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
* @since		1.5.0
*/
defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Layout\LayoutHelper;

$userCanRate = UserNotesHelper::userCanRate();

$this->jDoc->addScript('components/com_usernotes/static/js/upload5d.js', ['version' => 'auto']);
//$this->jDoc->addScript('components/com_usernotes/static/js/rating.js', ['version' => 'auto']);
if ($userCanRate) $this->jDoc->addScript('components/com_usernotes/static/js/rater1.js', ['version' => 'auto']);
$jslang = [
	'ru_sure' => Text::_('COM_USERNOTES_RU_SURE'),
	'fsz2big' => Text::_('COM_USERNOTES_FSZ2BIG'),
	'fbadtyp' => Text::_('COM_USERNOTES_FBADTYP'),
	'sure_del_att' => Text::_('COM_USERNOTES_SURE_DEL_ATT'),
	'rename_att' => Text::_('COM_USERNOTES_RENAME_ATT')
];
$jsvars = [
	'aBaseURL' => $this->aUrl('format=raw'),
	'itemID' => $this->item->itemID,
	'parentID' => $this->item->parentID,
	'contentID' => ($this->item->contentID?:0)
];
$jsicns = [
	'abrt' => HtmlUsernotes::getIcon('abrt')
];
$this->jDoc->addScriptDeclaration('var baseURL = "'.JUri::base().'";
var upldDestURL = "'.$this->aUrl('format=raw').'";
var fup_payload = {task:"EditRaw.attach",iID:'.$this->item->itemID.',cID:'.$this->item->contentID.',[Joomla.getOptions("csrf.token","")]:"1"};
var uploadMaxFilesize = '.$this->maxUploadBytes.';
	UNote.L = '.json_encode($jslang).';
	UNote.V = '.json_encode($jsvars).';
	UNote.I = '.json_encode($jsicns).';
	const _Id = (elm) => document.getElementById(elm);
');

$itemID = $this->item->itemID;

$prning = ($this->state->get('task', 0) === 'printNote');
//echo'<xmp>';var_dump($prning,$this->state->get('task', 0));echo'</xmp>';
if ($prning) echo '<button type="button" class="btn btn-primary" onclick="window.close();window.history.back();">'.Text::_('COM_USERNOTES_PRNDONE').'</button>';
// if not printing, accommodate targeted breadcrumb module
if (!$prning) echo HTMLHelper::_('content.prepare', '{loadposition usernotes_bc}');

$ratings = $this->params->get('ratings', 0);
$guestcom = $this->params->get('guest_comments', 0);

if (RJC_DBUG && !$this->qview) echo '<div class="RJDBG">'.json_encode($this->instanceObj).'</div>';

$bottoms = '';

if (!$prning && $ratings) {
	$bottoms .= '
//	let rating = _Id("unrating");
	//let r = new SimpleStarRating(rating);
//	let r = UNote.hoistRating(rating);
//	UNote.robj = r;
	';
	if (UserNotesHelper::userCanRate()) {
		$bottoms .= '
	//	rating.addEventListener("rate", UNote.rateEvt);
		let popr = _Id("popRate");	console.log(popr);
		popr.querySelector(".rating").addEventListener("rate", UNote.rateEvt);';
	} else {
	//	$bottoms .= 'r.disable();';
	}
}

if ($prning) $bottoms .= '
(function() {
	let bpd = false;
	let apd = false;

	const beforePrint = () => {
		if (bpd) return;
		bpd = true;
		console.log("Functionality to run before printing.");
	};

	const afterPrint = () => {
		if (apd) return;
		apd = true;
		console.log("Functionality to run after printing");
		window.close();
		window.history.back();
	};

	if (window.matchMedia) {
		let mediaQueryList = window.matchMedia("print");
		mediaQueryList.addListener( (mql) => {
			if (mql.matches) {
				beforePrint();
			} else {
				afterPrint();
			}
		});
	}

	window.addEventListener("beforeprint", beforePrint);
	window.addEventListener("afterprint", afterPrint);

	window.print();
}());
';
// include 'load1.php';
// kludge for quick view to indicate attachments
if ($this->qview && $this->attached) {
	// set a header to let the fetch know that there are attachments
	$this->app->setHeader('Has-Att', 1, true);
}
?>
<div id="container">
	<?php if (UserNotesHelper::userCanRate()) echo LayoutHelper::render('rater1'); ?>
	<div id="body">
		<?php if($ratings): ?>
		<div class="rated"><span id="numrats">(<?=$this->item->vcount?>)</span></div>
		<?php if (UserNotesHelper::userCanRate()): ?>
		<div id="ratep" class="rated active" onclick="UNote.popRate()"><?=HtmlUsernotes::itemStars($this->item)?></div>
		<?php else: ?>
		<div class="rated"><?=HtmlUsernotes::itemStars($this->item)?></div>
		<?php endif; ?>
		<?php endif; ?>
		<h3><?php if ($this->item->secured) echo HtmlUsernotes::getIcon('ulck','seclock'); ?> <?=$this->item->title?></h3>
		<div id="note"><?=$this->item->serial_content?></div>
	</div>
<?php if (!$this->qview): ?>
<?php if (!$prning): ?>
	<div id="attachments">
<?php if ($this->attached): ?>
		<?=HtmlUsernotes::att_list($this->attached,$this->item->contentID, ($this->access & ITM_CAN_EDIT+ITM_CAN_DELE))?>
<?php endif; ?>
	</div>
	<div class="footer">
		<?php
			echo HtmlUsernotes::prnActIcon($itemID,Text::_('COM_USERNOTES_PRNNOTE'));
			if ($guestcom || $this->item->cmntcnt || $this->access & ITM_CAN_COMMENT) echo HtmlUsernotes::cmntActIcon($itemID,Text::_('COM_USERNOTES_CMNTNOTE'.($this->item->cmntcnt?'S':'')),$this->item->cmntcnt);
		if ($this->access & ITM_CAN_EDIT) {
			echo HtmlUsernotes::edtActIcon($itemID,Text::_('COM_USERNOTES_EDTNOTE'));
			echo HtmlUsernotes::attActIcon($itemID,Text::_('COM_USERNOTES_ADDATCH'));
			echo HtmlUsernotes::movActIcon($itemID,Text::_('COM_USERNOTES_MOVNOTE'));
			echo HtmlUsernotes::toolActIcon($itemID,Text::_('COM_USERNOTES_SPCTOOL'));
		}
		if ($this->access & ITM_CAN_DELE) {
			echo HtmlUsernotes::delActIcon($itemID,Text::_('COM_USERNOTES_DELNOTE'));
		}
		?>
		&nbsp;<?=$this->footMsg?>
	</div>
	<?php if ($this->access & ITM_CAN_EDIT) : ?>
	<div id="filupld" class="uplddlog" style="display:none;">
		<span style="color:#36C;"><?=Text::_('COM_USERNOTES_MAXUPLD');?> <?=UserNotesHelper::formatBytes($this->maxUploadBytes)?></span>
		<input type="file" id="upload_field" name="attm[]" multiple="multiple" style="display:none" />
		<div id="dropArea" onclick="_Id('upload_field').click()"><?=Text::_('COM_USERNOTES_DROPFILS');?></div>
		<div id="result"></div>
		<div class="prgwrp"><div id="totprogress"></div></div>
		<div id="fprogress"></div>
		<hr />
		<button onclick="this.parentNode.style.display='none'">Close</button>
	</div>
	<div id="putmenu" class="pum" style="display:none" onmouseover="UNote.mcancelclosetime()" onmouseout="UNote.mclosetime()">
		<ul id="spum">
			<li><a href="#" onclick="UNote.toolAct(event,'dofraction')" title="<?=Text::_('COM_USERNOTES_DOFRACT');?>"><?=Text::_('COM_USERNOTES_DOFRACTZ');?></a></li>
			<li><a href="#" onclick="UNote.toolAct(event,'unfraction')" title="<?=Text::_('COM_USERNOTES_UNFRACT');?>"><?=Text::_('COM_USERNOTES_UNFRACT');?></a></li>
		<?php if ($this->attached): ?>
			<li><a href="#" onclick="UNote.toolAct(event,'deleteAttachments')" title="<?=Text::_('COM_USERNOTES_DELAATTS');?>" data-sure="<?=strtolower(Text::_('COM_USERNOTES_DELAATTS'));?>"><?=Text::_('COM_USERNOTES_DEL_ATTS');?></a></li>
		<?php endif; ?>
		</ul>
	</div>
	<div id="popRate" class="popRate" style="display:none">
		<span class="rating" data-default-rating="0"></span>
	</div>
	<?php endif; ?>
<?php endif; //prning?>
</div>
<div style="display:none">
<form name="actForm" action="<?=$this->aUrl('')?>" method="POST">
<input type="hidden" name="task">
<input type="hidden" name="iid" value="<?=$this->item->itemID?>">
<?php echo HTMLHelper::_('form.token'); ?>
</form>
</div>
<?php if ($this->attached): ?>
<iframe id="dnldf" style="display:none;"></iframe>
<?php endif; ?>
<?php if ($bottoms) echo '<script>'.$bottoms.'</script>'; ?>
<?php endif; //qview?>
<?php if (true || $use_comments) {
	echo LayoutHelper::render('comments', ['cancmnt'=>$cancmnt ?? true]);
	if (true || $cancmnt) {
		echo LayoutHelper::render('comment',['userid'=>$this->userid,'view'=>$this]);
	}
}
