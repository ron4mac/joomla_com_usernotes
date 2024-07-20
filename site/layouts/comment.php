<?php
/**
* @package		com_usernotes
* @copyright	Copyright (C) 2024 RJCreations. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
* @since		1.5.0
*/
defined('JPATH_BASE') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Session\Session;

extract($displayData);	//userid,view

//if ((int)JVERSION<4) {
//	HTMLHelper::_('behavior.formvalidator');
//} else {
//	$wa = $view->document->getWebAssetManager();
//	$wa->useScript('keepalive')->useScript('form.validate');
//}

$nextra = $userid ? '<input type="hidden" name="name" value="?" />' : '<br><label>Name <input type="text" name="name" value="" onkeyup="UNote.watchcmnt()" /></label>';

$mmdl =  HTMLHelper::_(
	'bootstrap.renderModal',
	'comment-modal', // selector
	array( // options
		'title'  => Text::_('COM_USERNOTES_COMMENT_TITLE'),
		'footer' => '<button type="button" class="btn btn-secondary" '.M34C::bs('dismiss').'="modal">Close</button>
					<button type="button" class="btn btn-primary" id="cmntSbb" onclick="UNote.submitComment(this)" disabled>Submit Comment</button>',
		//'modalWidth' => 30
	),
	'<form id="newcmnt" method="POST" onsubmit="return false">
	<div class="new-comment"><textarea id="cmnt-text" name="cmntext" onkeyup="UNote.watchcmnt()"></textarea>'.$nextra.'</div>
	<input type="hidden" name="task" value="Raw.addComment" />
	<input type="hidden" name="'.Session::getFormToken().'" value="1" />
	</form>'
);
//remove the large modal css designation
echo str_replace(' modal-lg', '', $mmdl);
