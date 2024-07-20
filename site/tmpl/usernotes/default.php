<?php
/**
* @package		com_usernotes
* @copyright	Copyright (C) 2015-2024 RJCreations. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
* @since		1.4.4
*/
defined('_JEXEC') or die;

use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\HTML\Helpers\Bootstrap;

Bootstrap::modal('#qview-modal');

$jslang = [
		'no_sterm' => Text::_('COM_USERNOTES_NO_STERM'),
		'ru_sure' => Text::_('COM_USERNOTES_RU_SURE')
	];
$jsvars = [
	'aBaseURL' => $this->aUrl('format=raw'),
];
$jsicns = [
	'clip' => HtmlUsernotes::getIcon('clip')
];
$this->jDoc->addScriptDeclaration('UNote.L = '.json_encode($jslang).';
UNote.V = '.json_encode($jsvars).';
UNote.I = '.json_encode($jsicns).';
');

if (/*$this->state->secured*/ $this->item && $this->item->secured && $_SERVER['SERVER_PORT'] != 443) {
	$this->nqMessage('<span style="color:red">'.Text::_('COM_USERNOTES_NOTICE_INSECURE').'</span>', 'warning');
}

if (RJC_DBUG) echo '<div class="RJDBG">'.json_encode($this->instanceObj).'</div>';

if (isset($this->posq)) {
	$svty = 'notice';
	$msg = Text::sprintf('COM_USERNOTES_NOTICE_QUOTA', UserNotesHelper::formatBytes($this->storSize, 1, ''), $this->posq * 100);
	if ($this->posq > 0.95) {
		$svty = 'warning';
		$msg = '<span style="color:red">'.$msg.'</span>';
	}
	$this->nqMessage($msg, $svty);
}

if ($this->state->secured && $_SERVER['SERVER_PORT'] != 443) {
	$securl = $this->cparams->get('secureurl','');
	if (!$securl) {
		$securl = substr(strstr(dirname(JUri::root()), '://'), 3);
	}
	//var_dump($hostname,$paths);var_dump($_SERVER);
	echo '<div style="background-color:red;color:white;">';
	echo Text::_('COM_USERNOTES_NOTICE_INSECURE');
	echo ' <a href="https://'.$securl.$_SERVER['REQUEST_URI'].'" style="color:yellow">'.Text::_('COM_USERNOTES_CONNECT_SECURELY').'</a>';
	echo '</div>';
}

$ratings = $this->mparams->get('ratings', false);

// accommodate targeted breadcrumb module
echo HTMLHelper::_('content.prepare', '{loadposition usernotes_bc}');
// display the search field
echo HtmlUsernotes::searchField($this->parentID);
?>
<style>
	.rated, #ratep { float: right; }
</style>
<div id="container">
	<div id="body">
	<div id="itemsList">
	<?php foreach($this->items as $item): ?>
		<div class="item">
			<?=HtmlUsernotes::itemQview($item, $ratings);?>
		</div>
	<?php endforeach; ?>
	</div>
	</div>
	<div class="footer">
		<?php if ($this->access & ITM_CAN_CREA) : ?>
		<?=HtmlUsernotes::newActIcon($this->parentID, Text::_('COM_USERNOTES_EDIT_FORM_CREATE'))?><?=HtmlUsernotes::fNewActIcon($this->parentID.'&type=f',Text::_('COM_USERNOTES_EDIT_FORM_CREATE_F'))?>
		<?php else : ?>
		&nbsp;
		<?php endif;?>
		<?php if ($this->parentID) {
			if ($this->access & ITM_CAN_DELE) echo HtmlUsernotes::fDelActIcon($this->parentID,Text::_('COM_USERNOTES_EDIT_FORM_DELETE_F'));
			if ($this->access & ITM_CAN_EDIT) echo HtmlUsernotes::fEdtActIcon($this->parentID,Text::_('COM_USERNOTES_EDIT_FORM_EDIT_F'));
			}
		?>
	</div>
</div>
<div style="display:none">
<form name="actForm" action="<?=$this->aUrl('')?>" method="POST">
<input type="hidden" name="task">
<input type="hidden" name="iid" value="<?=$this->item->itemID?>">
<?php echo HTMLHelper::_('form.token'); ?>
</form>
</div>
<?php if ($this->cparams->get('show_version',0) && !$this->parentID): ?>
<div class="verdisp">
	Version: <?php echo $this->cparams->get('version'); ?>
</div>
<?php endif; ?>
<?php
	if ($this->access & (ITM_CAN_CREA + ITM_CAN_EDIT + ITM_CAN_DELE)) echo LayoutHelper::render('folder', ['view'=>$this, 'vitm'=>$this->item, 'create'=>true]);
?>
