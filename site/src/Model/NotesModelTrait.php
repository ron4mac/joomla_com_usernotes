<?php
/**
* @package		com_usernotes
* @copyright	Copyright (C) 2015-2026 RJCreations. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
* @since		1.6.1
*/
namespace RJCreations\Component\Usernotes\Site\Model;

trait NotesModelTrait {
	public function pathWays ($to)
	{
		$db = $this->getDatabase();
		$crums = [];
		while ($to) {
			$db->setQuery('SELECT title,parentID,secured FROM notes WHERE itemID='.$to);
			$r = $db->loadAssoc();
			if ($r['secured']) {
				$r['title'] = base64_decode($r['title']);
			}
			array_unshift($crums, [$r['title'], 'pid='.$to]);
			$to = $r['parentID'];
		}
		return $crums;
	}
}
