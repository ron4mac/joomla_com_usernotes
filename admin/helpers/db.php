<?php
/**
 * @package    com_usernotes
 *
 * @copyright  Copyright (C) 2016 RJCreations - All rights reserved.
 * @license    GNU General Public License version 3 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

require_once JPATH_COMPONENT_SITE.'/classes/note_class.php';

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
	//	}

		// add `secured` column to `notes`
		$db->setQuery('ALTER TABLE notes ADD COLUMN secured BOOLEAN DEFAULT NULL');
		@$db->execute();
}
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

}