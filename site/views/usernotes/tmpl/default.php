<?php
defined('_JEXEC') or die;

JHtml::stylesheet('components/com_usernotes/static/css/oopim.css');
JHtml::_('jquery.framework', false);
$jdoc = JFactory::getDocument();
$jdoc->addScript('components/com_usernotes/static/js/oopim.js');
$jdoc->addScript('components/com_usernotes/static/js/notesview.js');

if (/*$this->state->secured*/ $this->item && $this->item->secured && $_SERVER['SERVER_PORT'] != 443) {
	//JError::raiseNotice(100, 'You do not have a secure connection!', 'error');
	JFactory::getApplication()->enqueueMessage('<span style="color:red">'.JText::_('You do not have a secure connection!').'</span>', 'warning');
	//echo '<div style="background-color:red;color:white;">WARNING: You do not have a secure connection!</div>';
}

if (isset($this->posq)) {
	JFactory::getApplication()->enqueueMessage(JText::sprintf('Storage usage is at %s%% of quota!',$this->posq), 'notice');
}
//JFactory::getApplication()->enqueueMessage('<span style="color:red">'.JText::_('Storage usage is at 90% of quota!').'</span>', 'warning');
//JError::raiseNotice(100, 'Storage usage is at 90% of quota!');
//var_dump($this->params);echo'<br /><br />';var_dump(JComponentHelper::getParams('com_usernotes'));

if ($this->state->secured && $_SERVER['SERVER_PORT'] != 443) {
	$hostname = php_uname('n');
	//$hostname = str_replace('ehub','secure',$hostname);
	$paths = explode('/',__FILE__);
	//var_dump($hostname,$paths);var_dump($_SERVER);
	echo '<div style="background-color:red;color:white;">';
	echo 'WARNING: You do not have a secure connection!';
	echo '<a href="https://'.$hostname.'/~'.$paths[2].$_SERVER['REQUEST_URI'].'">[connect securely]</a>';
	echo '</div>';
}

?>
<div class="search">
	<form name="sqry" onsubmit="return Oopim.performSearch(this,<?=$this->parentID?>)">
		<input type="hidden" name="task" value="search" />
		<input type="search" name="sterm" results="10" autosave="oopim_notes" placeholder="Search..." />
	</form>
</div>
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
