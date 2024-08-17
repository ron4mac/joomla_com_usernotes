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
use Joomla\CMS\HTML\Helpers\Bootstrap;
use RJCreations\Component\Usernotes\Site\Helper\HtmlUsernotes;

if (RJC_DBUG) echo '<div class="RJDBG">'.json_encode($this->instanceObj).'</div>';

$jslang = [
		'no_sterm' => Text::_('COM_USERNOTES_NO_STERM'),
		'ru_sure' => Text::_('COM_USERNOTES_RU_SURE')
	];
$jsicns = [
	'clip' => HtmlUsernotes::getIcon('clip')
];
$this->jDoc->addScriptDeclaration('UNote.L = '.json_encode($jslang).';
UNote.I = '.json_encode($jsicns).';
');

// accommodate targeted breadcrumb module
echo HTMLHelper::_('content.prepare', '{loadposition usernotes_bc}');
// display the search field
HtmlUsernotes::searchField(!empty($this->parentID) ? $this->parentID : 0, $this->sterm);

Bootstrap::modal('#qview-modal');
?>
<style>
	.rated, #ratep { float: right; }
</style>
<div id="container" class="searchr">
	<div id="body">
	<div id="itemsList">
	<?php
	foreach($this->items as $item) {
		echo '<div class="item">'. HtmlUsernotes::itemQview($item, true).'</div>';
	}
	?>
	</div>
	</div>
	<div class="footer">&nbsp;</div>
</div>
