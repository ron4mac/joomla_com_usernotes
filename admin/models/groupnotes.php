<?php
/**
 * @package    com_usernotes
 *
 * @copyright  Copyright (C) 2016-2019 RJCreations - All rights reserved.
 * @license    GNU General Public License version 3 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

require_once 'usernotes.php';

class UsernotesModelGroupnotes extends UsernotesModelUsernotes
{
	protected $relm = 'g';
}
