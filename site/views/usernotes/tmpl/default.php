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
//JFactory::getApplication()->enqueueMessage('<span style="color:red">'.JText::_('Storage usage is at 90% of quota!').'</span>', 'warning');
//JError::raiseNotice(100, 'Storage usage is at 90% of quota!');
//var_dump($this->params);echo'<br /><br />';var_dump(JComponentHelper::getParams('com_usernotes'));

if ($this->state->secured && $_SERVER['SERVER_PORT'] != 443) {
	$securl = $this->cparams->get('secureurl','');
	if (!$securl) {
		$hostname = php_uname('n');
		$hostaddr = $_SERVER['SERVER_ADDR'];
		$paths = explode('/',__FILE__);
		$securl = $hostaddr.'/~'.$paths[2];
	}
	//var_dump($hostname,$paths);var_dump($_SERVER);
	echo '<div style="background-color:red;color:white;">';
	echo 'WARNING: You do not have a secure connection!';
	echo '<a href="https://'.$securl.$_SERVER['REQUEST_URI'].'" style="color:yellow">[connect securely]</a>';
	echo '</div>';
}
echo JHtml::_('content.prepare', '{loadposition usernotes_bc}');
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
		<?=JHtml::_('usernotes.newActIcon',$this->parentID,'Add new note')?><?=JHtml::_('usernotes.fNewActIcon',$this->parentID.'&type=f','Add new folder')?>
		<?php else : ?>
		&nbsp;
		<?php endif;?>
		<?php if ($this->parentID) {
			if ($this->access & ITM_CAN_DELE) echo JHtml::_('usernotes.fDelActIcon',$this->parentID,'Delete this folder');
			if ($this->access & ITM_CAN_EDIT) echo JHtml::_('usernotes.fEdtActIcon',$this->parentID,'Edit this folder');
			}
		?>
	</div>
</div>
