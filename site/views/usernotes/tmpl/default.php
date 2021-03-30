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
$jslang = [
		'no_sterm' => Text::_('COM_USERNOTES_NO_STERM'),
		'ru_sure' => Text::_('COM_USERNOTES_RU_SURE')
	];
$this->jDoc->addScriptDeclaration('UNote.L = '.json_encode($jslang).';
');

if (/*$this->state->secured*/ $this->item && $this->item->secured && $_SERVER['SERVER_PORT'] != 443) {
	//JError::raiseNotice(100, 'You do not have a secure connection!', 'error');
	$this->nqMessage('<span style="color:red">'.Text::_('COM_USERNOTES_NOTICE_INSECURE').'</span>', 'warning');
	//echo '<div style="background-color:red;color:white;">WARNING: You do not have a secure connection!</div>';
}

if (RJC_DBUG) echo '<div>'.$this->instance.'</div>';

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

// accommodate targeted breadcrumb module
echo HTMLHelper::_('content.prepare', '{loadposition usernotes_bc}');
// display the search field
echo HTMLHelper::_('usernotes.searchField', $this->parentID);
?>
<div id="container" style="margin-top:1em;">
	<div id="body">
	<ul id="itemsList">
	<?php foreach($this->items as $item): ?>
		<li class="<?=($item->isParent?'note fold':'note docu').($item->shared?'_s':'')?>">
			<?=HTMLHelper::_('usernotes.itemLink', $item);?>
		</li>
	<?php endforeach; ?>
	</ul>
	</div>
	<div class="footer">
		<?php if ($this->access & ITM_CAN_CREA) : ?>
		<?=HTMLHelper::_('usernotes.newActIcon',$this->parentID, Text::_('COM_USERNOTES_EDIT_FORM_CREATE'))?><?=HTMLHelper::_('usernotes.fNewActIcon', $this->parentID.'&type=f',Text::_('COM_USERNOTES_EDIT_FORM_CREATE_F'))?>
		<?php else : ?>
		&nbsp;
		<?php endif;?>
		<?php if ($this->parentID) {
			if ($this->access & ITM_CAN_DELE) echo HTMLHelper::_('usernotes.fDelActIcon', $this->parentID,Text::_('COM_USERNOTES_EDIT_FORM_DELETE_F'));
			if ($this->access & ITM_CAN_EDIT) echo HTMLHelper::_('usernotes.fEdtActIcon', $this->parentID,Text::_('COM_USERNOTES_EDIT_FORM_EDIT_F'));
			}
		?>
	</div>
</div>
<?php if ($this->cparams->get('show_version',0) && !$this->parentID): ?>
<div class="verdisp">
	Version: <?php echo $this->cparams->get('version'); ?>
</div>
<?php endif; ?>
