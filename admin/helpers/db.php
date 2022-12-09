<?php
/**
* @package		com_usernotes
* @copyright	Copyright (C) 2015-2022 RJCreations. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
*/
defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

// require_once JPATH_COMPONENT_SITE.'/classes/note_class.php';

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


	public static function getInfo ($udbPath)
	{
		//$udbPath .= '/usernotes.db3';
		if (!file_exists($udbPath)) return [];
		$size = filesize($udbPath);
		$db = JDatabaseDriver::getInstance(['driver'=>'sqlite', 'database'=>$udbPath]);
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

/*
	public static function convertDb ($udbPath)
	{
		if (!file_exists($udbPath)) return;
		$db = JDatabaseDriver::getInstance(['driver'=>'sqlite', 'database'=>$udbPath.'/usernotes.db3']);
		$dbver = $db->setQuery('PRAGMA user_version')->loadResult();
		$msgs = [];
		if (file_exists(JPATH_COMPONENT_ADMINISTRATOR.'/sql/upd_'.$dbver.'.sql')) {
			$execs = explode(';', file_get_contents(JPATH_COMPONENT_ADMINISTRATOR.'/sql/upd_'.$dbver.'.sql'));
			foreach ($execs as $exec) {
				$msg = null;
				$exec = trim($exec);
				if ($exec && $exec[0] != '#') $msg = self::dbnofail($db, $exec);
				if ($msg) $msgs[] = $msg;
			}
		}
		return $msgs;
	}


	public static function ccconvertDb ($udbPath)
	{
		if (!file_exists($udbPath)) return;
		$attsDir = $udbPath.'/attach/';
		$db = JDatabaseDriver::getInstance(['driver'=>'sqlite', 'database'=>$udbPath.'/usernotes.db3']);

		$tbls = $db->getTableList();
		if (!in_array('fileatt', $tbls)) {
			// convert 'attached' table to 'fileatt' table with file sizes
			$db->setQuery('CREATE TABLE IF NOT EXISTS fileatt (contentID INTEGER NOT NULL, fsize INTEGER, attached TEXT)');
			$db->execute();
			$olds = $db->setQuery('SELECT * FROM attach')->loadAssocList();
			foreach ($olds as $old) {
				$atts = unserialize($old['attached']);
				foreach ($atts as $att) {
					$atfp = $old['contentID'].'/'.$att;
					$atsz = file_exists($attsDir.$atfp) ? filesize($attsDir.$atfp) : 9999999999;
					$db->setQuery('INSERT INTO fileatt (contentID, fsize, attached) VALUES ('
						.$old['contentID']
						.', '.$atsz
						.', '.$db->quote($att)
						.')');	//var_dump((string)$db);jexit();
					$db->execute();
				}
			}
			// remove the old 'attached table'
			$db->setQuery('DROP TABLE IF EXISTS attach')->execute();
	
			// create view to sum the file sizes
			$db->setQuery('CREATE VIEW attsizsum AS SELECT SUM(fsize) AS totatt FROM fileatt');
			$db->execute();
		}

		// add `secured` column to `notes`
		self::dbnofail($db, 'ALTER TABLE notes ADD COLUMN secured BOOLEAN DEFAULT NULL');
		// add `vtotal` and `vcount` column to `notes` for star rating
		self::dbnofail($db, 'ALTER TABLE notes ADD COLUMN vcount INTEGER DEFAULT 0');
		self::dbnofail($db, 'ALTER TABLE notes ADD COLUMN vtotal INTEGER DEFAULT 0');
		self::dbnofail($db, 'CREATE TABLE IF NOT EXISTS uratings (iid INTEGER,uid INTEGER,rdate DATETIME DEFAULT CURRENT_TIMESTAMP)');
		self::dbnofail($db, 'CREATE TABLE IF NOT EXISTS gratings (iid INTEGER,ip INTEGER,rdate DATETIME DEFAULT CURRENT_TIMESTAMP)');


		// convert all content
		$qry = $db->getQuery(true)
			->select('n.itemID, n.title, n.contentID, c.serial_content')
			->from('notes AS n')
			->join('LEFT', 'content AS c on c.contentID = n.contentID');
		$off = 0; $lim = 10;
		while (true) {
			$qry->setLimit($lim, $off);
			$rows = $db->setQuery($qry)->loadAssocList();
			if (!$rows) break;
			foreach ($rows as $row) {
				if ($nm = @unserialize($row['serial_content'])) {
					if ($nm instanceof Secured_model) {
						$row['serial_content'] = $nm->rawtext();
						$db->setQuery('UPDATE notes SET title='.$db->quote(base64_encode($row['title'])).', secured=1 WHERE itemID='.$row['itemID'])->execute();
					} else {
						$row['serial_content'] = $nm->rendered();
					}
					$db->setQuery('UPDATE content SET serial_content='.$db->quote($row['serial_content']).' WHERE contentID='.$row['contentID'])->execute();
				}
			//	echo $row['contentID'].substr($row['serial_content'], 0, 10).'<br />';
			}
			$off += $lim;
		}
		//jexit();
	}

	private static function dbnofail ($db, $q)
	{
		try {
			$db->setQuery($q);
			@$db->execute();
		} catch (Exception $e) {
			return $e->getMessage();
		}
	}
*/
}