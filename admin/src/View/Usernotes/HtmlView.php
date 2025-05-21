<?php
/**
* @package		com_usernotes
* @copyright	Copyright (C) 2015-2024 RJCreations. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
* @since		1.5.0
*/
namespace RJCreations\Component\Usernotes\Administrator\View\Usernotes;

defined('_JEXEC') or die;

require_once JPATH_BASE . '/components/com_usernotes/src/View/notesview.php';

/**
 * View class for a list of user schedules.
 */
class HtmlView extends \UsernotesView
{
	protected $relm = 'user';
}
