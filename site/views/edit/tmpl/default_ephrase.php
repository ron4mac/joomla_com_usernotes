<?php
/**
 * @package    com_usernotes
 *
 * @copyright  Copyright (C) 2016-2019 RJCreations - All rights reserved.
 * @license    GNU General Public License version 3 or later; see LICENSE.txt
 */
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
// accommodate targeted breadcrumb module
echo JHtml::_('content.prepare', '{loadposition usernotes_bc}');
?>
<div id="container">
	<div id="body">
		<h3><span class="icon-lock" style="font-size:.8em;opacity:0.5"></span><?=$this->item->title?></h3>
		<div class="ephrase">
			<form action="" method="POST">
				<label for="ephrase"><?=JText::_('');?>Encryption Phrase:</label>
				<input name="ephrase" type="password" id="ephrase" size=30 />
				<button type="submit" class="btn btn-primary">Submit</button>
			</form>
		</div>
	</div>
	<div class="footer">
		&nbsp;<?=$this->footMsg?>
	</div>
</div>
