<?php
defined('_JEXEC') or die;

require_once JPATH_BASE . '/components/com_usernotes/views/notesview.php';

/**
 * View class for a list of user schedules.
 */
class UsernotesViewGroupnotes extends UsernotesView
{
	protected $relm = 'group';
}
