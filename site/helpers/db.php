<?php
defined('_JEXEC') or die;

abstract class UserNotesHelperDb
{
	public static function buildDb ($db)
	{
		$execs = explode(';', file_get_contents(JPATH_COMPONENT_ADMINISTRATOR.'/sql/usernotes.sql'));
		foreach ($execs as $exec) {
			$exec = trim($exec);
			if ($exec[0] != '#') $db->setQuery($exec)->execute();
		}
	}
}
