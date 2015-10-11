<?php
defined('_JEXEC') or die;

abstract class UserNotesHelperUnote
{
	public static function hashCookieName ($v1=0, $v2=0)
	{
		$uid = JFactory::getUser()->get('id');
		return md5(implode(':', array($uid, $v1, $v2)));
	}

}