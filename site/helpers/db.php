<?php
/**
 * @package    com_usernotes
 *
 * @copyright  Copyright (C) 2016-2021 RJCreations - All rights reserved.
 * @license    GNU General Public License version 3 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

abstract class UserNotesHelperDb
{
	public static function buildDb ($db)
	{
		$execs = explode(';', file_get_contents(JPATH_COMPONENT_ADMINISTRATOR.'/sql/usernotes.sql'));
		foreach ($execs as $exec) {
			$exec = trim($exec);
			if (!empty($exec) && $exec[0] != '#') $db->setQuery($exec)->execute();
		}
	}

}
