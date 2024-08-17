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
use RJCreations\Component\Usernotes\Site\Helper\M34C;

extract($displayData);	//cancmnt

$addbutton = $cancmnt
	? '<button type="button" class="btn btn-info" '.M34C::bs('target').'="#comment-modal" '.M34C::bs('toggle').'="modal" '.M34C::bs('dismiss').'="modal">'.Text::_('COM_USERNOTES_COMMENTS_ADD').'</button>'
	: '';

echo HTMLHelper::_(
	'bootstrap.renderModal',
	'comments-modal', // selector
	array( // options
		'title'  => Text::_('COM_USERNOTES_COMMENTS_TITLE'),
		'footer' => '<button type="button" class="btn btn-secondary" '.M34C::bs('dismiss').'="modal">Close</button>' . $addbutton,
		//'modalWidth' => 30
	),
	'<div class="comments"></div>'
);
