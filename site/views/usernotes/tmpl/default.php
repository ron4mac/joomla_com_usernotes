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
$jdoc = JFactory::getDocument();
$jdoc->addScript('components/com_usernotes/static/js/oopim.js');
$jdoc->addScript('components/com_usernotes/static/js/notesview.js');
$jslang = array(
		'no_sterm' => JText::_('COM_USERNOTES_NO_STERM'),
		'ru_sure' => JText::_('COM_USERNOTES_RU_SURE')
	);
$jdoc->addScriptDeclaration('Oopim.L = '.json_encode($jslang).';
');

if (/*$this->state->secured*/ $this->item && $this->item->secured && $_SERVER['SERVER_PORT'] != 443) {
	//JError::raiseNotice(100, 'You do not have a secure connection!', 'error');
	JFactory::getApplication()->enqueueMessage('<span style="color:red">'.JText::_('COM_USERNOTES_NOTICE_INSECURE').'</span>', 'warning');
	//echo '<div style="background-color:red;color:white;">WARNING: You do not have a secure connection!</div>';
}

if (isset($this->posq)) {
	$svty = 'notice';
	$msg = JText::sprintf('COM_USERNOTES_NOTICE_QUOTA', UserNotesHelper::formatBytes($this->storSize, 1, ''), $this->posq * 100);
	if ($this->posq > 0.95) {
		$svty = 'warning';
		$msg = '<span style="color:red">'.$msg.'</span>';
	}
	JFactory::getApplication()->enqueueMessage($msg, $svty);
}

if ($this->state->secured && $_SERVER['SERVER_PORT'] != 443) {
	$securl = $this->cparams->get('secureurl','');
	if (!$securl) {
		$securl = substr(strstr(dirname(JUri::root()), '://'), 3);
	}
	//var_dump($hostname,$paths);var_dump($_SERVER);
	echo '<div style="background-color:red;color:white;">';
	echo JText::_('COM_USERNOTES_NOTICE_INSECURE');
	echo ' <a href="https://'.$securl.$_SERVER['REQUEST_URI'].'" style="color:yellow">'.JText::_('COM_USERNOTES_CONNECT_SECURELY').'</a>';
	echo '</div>';
}
// accommodate targeted breadcrumb module
echo JHtml::_('content.prepare', '{loadposition usernotes_bc}');
// display the search field
echo JHtml::_('usernotes.searchField', $this->parentID);
?>
<div id="container" style="margin-top:1em;">
	<div id="body">
	<ul id="itemsList">
	<?php foreach($this->items as $item): ?>
		<li class="<?=($item->isParent?'note fold':'note docu').($item->shared?'_s':'')?>">
			<?=JHtml::_('usernotes.itemLink', $item);?>
		</li>
	<?php endforeach; ?>
	</ul>
	</div>
	<div class="footer">
		<?php if ($this->access & ITM_CAN_CREA) : ?>
		<?=JHtml::_('usernotes.newActIcon',$this->parentID, JText::_('COM_USERNOTES_EDIT_FORM_CREATE'))?><?=JHtml::_('usernotes.fNewActIcon', $this->parentID.'&type=f',JText::_('COM_USERNOTES_EDIT_FORM_CREATE_F'))?>
		<?php else : ?>
		&nbsp;
		<?php endif;?>
		<?php if ($this->parentID) {
			if ($this->access & ITM_CAN_DELE) echo JHtml::_('usernotes.fDelActIcon', $this->parentID,JText::_('COM_USERNOTES_EDIT_FORM_DELETE_F'));
			if ($this->access & ITM_CAN_EDIT) echo JHtml::_('usernotes.fEdtActIcon', $this->parentID,JText::_('COM_USERNOTES_EDIT_FORM_EDIT_F'));
			}
		?>
	</div>
</div>
<?php if ($this->cparams->get('show_version',0) && !$this->parentID): ?>
<div class="verdisp">
	Version: <?php echo $this->cparams->get('version'); ?>
</div>
<?php endif; ?>
