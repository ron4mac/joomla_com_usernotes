<?php
/**
 * @package    com_usernotes
 *
 * @copyright  Copyright (C) 2016-2019 RJCreations - All rights reserved.
 * @license    GNU General Public License version 3 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

abstract class UserNotesHelperUnote
{
	public static function hashCookieName ($v1=0, $v2=0)
	{
		$uid = JFactory::getUser()->get('id');
		return md5(implode(':', array($uid, $v1, $v2)));
	}

}