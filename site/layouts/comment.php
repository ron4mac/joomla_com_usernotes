<?php
/**
* @package		com_usernotes
* @copyright	Copyright (C) 2024 RJCreations. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
* @since		1.4.0
*/
defined('JPATH_BASE') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Session\Session;

$mmdl =  HTMLHelper::_(
	'bootstrap.renderModal',
	'comment-modal', // selector
	array( // options
		'title'  => Text::_('COM_USERNOTES_COMMENT_TITLE'),
		'footer' => '<button type="button" class="btn btn-secondary" '.M34C::bs('dismiss').'="modal">Close</button>
					<button type="button" class="btn btn-primary" onclick="UNote.submitComment(this)">Submit Comment</button>',
		//'modalWidth' => 30
	),
	'<form id="newcmnt">
	<div class="new-comment"><textarea id="cmnt-text" name="cmntext"></textarea></div>
	<input type="hidden" name="task" value="addComment" />
	<input type="hidden" name="'.Session::getFormToken().'" value="1" />
	</form>'
);
//remove the large modal css designation
echo str_replace(' modal-lg', '', $mmdl);
