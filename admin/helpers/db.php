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

	public static function convertDb ($udbPath)
	{
		if (!file_exists($udbPath)) return;
		$attsDir = $udbPath.'/attach/';
		$db = JDatabaseDriver::getInstance(array('driver'=>'sqlite', 'database'=>$udbPath.'/usernotes.db3'));

		$tbls = $db->getTableList();
		if (in_array('fileatt', $tbls)) {
			return;
		}

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
		//$db->setQuery('DROP TABLE IF EXISTS attach')->execute();

		// create view to sum the file sizes
		$db->setQuery('CREATE VIEW attsizsum AS SELECT SUM(fsize) AS totatt FROM fileatt');
		$db->execute();

		// add `secured` column to `notes`
		$db->setQuery('ALTER TABLE notes ADD COLUMN secured BOOLEAN DEFAULT NULL');
		$db->execute();
	}

}