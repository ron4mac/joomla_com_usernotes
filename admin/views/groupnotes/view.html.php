<?php
/**
* @package		com_usernotes
* @copyright	Copyright (C) 2015-2022 RJCreations. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
*/
defined('_JEXEC') or die;

require_once JPATH_BASE . '/components/com_usernotes/views/notesview.php';

/**
 * View class for a list of user notes.
 */
class UsernotesViewGroupnotes extends UsernotesView
{
	protected $relm = 'group';
}
