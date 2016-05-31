<?php
/**
 * @package    com_usernotes
 *
 * @copyright  Copyright (C) 2016 RJCreations - All rights reserved.
 * @license    GNU General Public License version 3 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

require_once JPATH_BASE . '/components/com_usernotes/views/notesview.php';

/**
 * View class for a list of user schedules.
 */
class UsernotesViewGroupnotes extends UsernotesView
{
	protected $relm = 'group';
}
