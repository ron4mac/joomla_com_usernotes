<?php
defined('_JEXEC') or die;

JHtml::stylesheet('components/com_usernotes/static/css/oopim.css');
JHtml::_('jquery.framework', false);
$jdoc = JFactory::getDocument();
$jdoc->addScript('components/com_usernotes/static/js/oopim.js');
$jdoc->addScript('components/com_usernotes/static/js/notesview.js');

//var_dump($this->items);
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
