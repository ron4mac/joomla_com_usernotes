<?php
/**
* @package		com_usernotes
* @copyright	Copyright (C) 2015-2024 RJCreations. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
* @since		1.5.0
*/
namespace RJCreations\Component\Usernotes\Administrator\Helper;

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseDriver;

abstract class UsernotesHelperDb
{
	public static function buildDb ($db)
	{
		$execs = explode(';', file_get_contents(JPATH_COMPONENT_ADMINISTRATOR.'/sql/usernotes.sql'));
		foreach ($execs as $exec) {
			$exec = trim($exec);
			if ($exec[0] != '#') $db->setQuery($exec)->execute();
		}
	}


	public static function getInfo ($udbPath)
	{
		if (!file_exists($udbPath)) return [];
		$size = filesize($udbPath);
		$db = DatabaseDriver::getInstance(['driver'=>'sqlite', 'database'=>$udbPath]);
		$tbls = $db->getTableList();	//return print_r($tbls);
		if (in_array('attach', $tbls)) return Text::_('COM_USERNOTES_OLD_DB');
		$notescols = $db->setQuery('PRAGMA table_info(notes)')->loadRowList(1);
		if (!isset($notescols['vtotal'])) return Text::_('COM_USERNOTES_OLD_DB');
		//return '<pre>'.print_r($notescols,true).'</pre>';
		
		$atsz = $db->setQuery('SELECT totatt FROM attsizsum')->loadResult();
		$size += $atsz;
		$notes = $db->setQuery('SELECT COUNT(*) FROM notes')->loadResult();
		$atts = $db->setQuery('SELECT COUNT(*) FROM fileatt')->loadResult();
		$dbv = $db->setQuery('PRAGMA user_version')->loadResult();
		return ['size'=>$size,'notes'=>$notes,'atts'=>$atts,'hasold'=>false,'dbv'=>$dbv];
	}

}