<?php
/**
* @package		com_usernotes
* @copyright	Copyright (C) 2024 RJCreations. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
* @since		1.4.4
*/
defined('_JEXEC') or die;

use Joomla\CMS\Factory;

class UserNotesModelSocial extends UserNotesModelUserNotes
{

	// store submitted ratings for items
	// return average of all submitted ratings
	public function rate ($iid, $val)
	{
		// add a new rating to an item's collective rating
		$db = $this->getDbo();
		$db->transactionStart();
		if ($val) {
			$db->setQuery('SELECT ratecnt,ratetot FROM meedyaitems WHERE id='.$iid);
			$r = $db->loadAssoc();
			if (!$r) return -1;
			$rcnt = $r['ratecnt'] + 1;
			$rtot = $r['ratetot'] + $val;
		} else {
			$rcnt = $rtot = 0;
		}
		$db->setQuery('UPDATE meedyaitems SET ratecnt='.$rcnt.',ratetot='.$rtot.' WHERE id='.$iid)->execute();
		$db->transactionCommit();

		// if clearing, delete history and return zero
		if (!$rcnt) {
			$db->setQuery('DELETE FROM uratings WHERE iid='.$iid)->execute();
			$db->setQuery('DELETE FROM gratings WHERE iid='.$iid)->execute();
			return 0;
		}

		// MAY WANT TO PROVIDE A METHOD TO CLEAR AWAY OLD RECORDS (maybe older than 90 days: time()-7776000)
//		if (false) {
			$bfd = time()-7776000;	// 90 days (could make configurable)
			$db->setQuery('DELETE FROM uratings WHERE rdate<'.$bfd)->execute();
			$db->setQuery('DELETE FROM gratings WHERE rdate<'.$bfd)->execute();
//		}

		// remember where the rating came from to inhibit multiples
		$uid = Factory::getUser()->get('id');
		if ($uid) {
			$db->setQuery('INSERT INTO uratings (iid,uid,rdate) VALUES('.$iid.','.$uid.','.time().')');
		} else {
			$db->setQuery('INSERT INTO gratings (iid,ip,rdate) VALUES('.$iid.',\''.$_SERVER['REMOTE_ADDR'].'\','.time().')');		// @@@@@ MAYBE DO THIS REGARDLESS @@@@@
		}
		$db->execute();

		// return average
		return $rtot/$rcnt;
	}

	// check whether a submitter has already rated an item
	// returns false if there has been no recorded submission
	public function rateChk ($iid)
	{
		$db = $this->getDbo();
		$uid = Factory::getUser()->get('id');
		if ($uid) {
			$db->setQuery('SELECT rdate FROM uratings WHERE iid='.$iid.' AND uid='.$uid);
		} else {
			$db->setQuery('SELECT rdate FROM gratings WHERE iid='.$iid.' AND ip=\''.$_SERVER['REMOTE_ADDR'].'\'');
		}
		return $db->loadResult();
	}

	// get all the comments for and item
	public function getComments ($iid)
	{
		$db = $this->getDbo();
		$db->setQuery('SELECT * FROM comments WHERE noteID='.$iid.' ORDER BY `ctime` DESC');
		return $db->loadAssocList();
	}

	// add a new comment
	public function addComment ($iid, $cmnt, $uid=0, $who='')
	{
		$db = $this->getDbo();
		$db->transactionStart();
		$db->setQuery('INSERT INTO comments (noteID,uID,who,ctime,comment) VALUES('.$iid.','.$uid.','.$db->quote($who).','.time().','.$db->quote($cmnt).')')->execute();
		$db->setQuery('SELECT cmntcnt FROM notes WHERE itemID='.$iid);
		$cnt = $db->loadResult();
		$db->setQuery('UPDATE notes SET cmntcnt='.(++$cnt).' WHERE itemID='.$iid)->execute();
		$db->transactionCommit();
		return $cnt;
	}

	// delete a comment
	public function delComment ($cid)
	{
		$db = $this->getDbo();
		$db->transactionStart();
		$db->setQuery('SELECT noteID FROM comments WHERE cmntID='.$cid);
		$nid = $db->loadResult();
		$db->setQuery('SELECT cmntcnt FROM notes WHERE itemID='.$nid);
		$cnt = $db->loadResult();
		$db->setQuery('UPDATE notes SET cmntcnt='.(--$cnt).' WHERE itemID='.$nid)->execute();
		$db->setQuery('DELETE FROM comments WHERE cmntID='.$cid)->execute();
		$db->transactionCommit();
		return $cnt;
	}

}
