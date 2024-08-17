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
use RJCreations\Component\Usernotes\Site\Helper\HtmlUsernotes;

$jslang = [
	'ru_sure' => Text::_('COM_USERNOTES_RU_SURE')
];
$jsvars = [
	'aBaseURL' => $this->aUrl('format=raw'),
];

$this->jDoc->addScriptDeclaration('var baseURL = "'.JUri::base().'";
	UNote.L = '.json_encode($jslang).';
	UNote.V = '.json_encode($jsvars).';
');
$itemID = $this->item->itemID;
// accommodate targeted breadcrumb module
echo HTMLHelper::_('content.prepare', '{loadposition usernotes_bc}');
?>
<div id="container">
	<div id="body">
		<h3><?php echo HtmlUsernotes::getIcon('lock','seclock'); ?> <?=$this->item->title?></h3>
		<div class="ephrase">
			<form action="" method="post" class="form-validate">
				<?php echo $this->form->renderFieldset('ephrase')?>
				<button type="submit" class="btn btn-primary"><?=Text::_('JSUBMIT');?></button>
			</form>
		</div>
	</div>
	<div class="footer">
		<?php
		if ($this->access & ITM_CAN_EDIT) {
			echo HtmlUsernotes::movActIcon($itemID,Text::_('COM_USERNOTES_MOVNOTE'));
		}
		if ($this->access & ITM_CAN_DELE) {
			echo HtmlUsernotes::delActIcon($itemID,Text::_('COM_USERNOTES_DELNOTE'));
		}
		?>
		&nbsp;<?=$this->footMsg?>
	</div>
</div>
