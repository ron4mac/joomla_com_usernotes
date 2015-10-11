<?php
defined('_JEXEC') or die;
JHtml::stylesheet('components/com_usernotes/static/css/oopim.css');
JHtml::stylesheet('components/com_usernotes/static/css/pumenu.css');
JHtml::_('jquery.framework');
$jdoc = JFactory::getDocument();
$jdoc->addScriptDeclaration('var baseURL = "'.JUri::base().'";
var aBaseURL = "'.JUri::base().'index.php?option=com_usernotes&format=raw&unID='.urlencode($this->notesID).'&task=";
var itemID = '.$this->item->itemID.';
var notesID = "'.urlencode($this->notesID).'";
var parentID = '.$this->item->parentID.';
var contentID = '.$this->item->contentID.';
');
$itemID = $this->item->itemID;
?>
<div id="container">
	<div id="body">
		<h3><?=$this->item->title?></h3>
		<div class="ephrase">
			<form action="" method="POST">
				<label for="ephrase"><?=JText::_('');?>Encryption Phrase:</label>
				<input name="ephrase" type="text" id="ephrase" size=30 />
				<button type="submit" class="btn btn-primary">Submit</button>
			</form>
		</div>
	</div>
	<div class="footer">
		&nbsp;<?=$this->footMsg?>
	</div>
</div>
