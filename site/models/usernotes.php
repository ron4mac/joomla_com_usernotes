<?php
/**
* @package		com_usernotes
* @copyright	Copyright (C) 2015-2022 RJCreations. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
*/
defined('_JEXEC') or die;

use Joomla\CMS\Factory;

JLoader::register('UserNotesHelper', JPATH_COMPONENT_ADMINISTRATOR.'/helpers/usernotes.php');

class UserNotesModelUserNotes extends JModelList
{
	const DBFILE = '/usernotes.db3';
	protected $instanceObj;
	protected $_storPath = null;

	public function __construct ($config = [])
	{
		$this->instanceObj = UserNotesHelper::getInstanceObject();
		$this->_storPath = UserNotesHelper::userDataPath();
		$udbPath = $this->_storPath.self::DBFILE;
		$doInit = !file_exists($udbPath);
		
		$option = ['driver'=>'sqlite', 'database'=>$udbPath];
		$db = JDatabaseDriver::getInstance($option);
		$db->connect();
		$db->getConnection()->sqliteCreateFunction('b64d', 'base64_decode', 1);

		if ($doInit) {
			require_once JPATH_COMPONENT.'/helpers/db.php';
			UserNotesHelperDb::buildDb($db);
		}

		$config['dbo'] = $db;
		parent::__construct($config);
	}

	// an sqlite extended function to search a field for search terms
	protected $smod = '', $sstrs = []; 
	public function sfunc ($str) {
		switch ($this->smod) {
			case '|':
				foreach ($this->sstrs as $sstr) {
					if (stripos($str, $sstr) !== false) return true;
				}
				break;
			case '&':
				foreach ($this->sstrs as $sstr) {
					if (stripos($str, $sstr) === false) return false;
				}
				return true;
				break;
			default:
				if (stripos($str, $this->sstrs[0]) !== false) return true;
		}
		return false;
	}

	public function search ($sterm)
	{
		if (strpos($sterm, ' OR ') > 0) {
			$this->smod = '|';
			$ss = explode(' OR ', $sterm);
			foreach ($ss as $s) {
				$this->sstrs[] = trim($s);
			}
		} elseif (strpos($sterm, ' AND ') > 0) {
			$this->smod = '&';
			$ss = explode(' AND ', $sterm);
			foreach ($ss as $s) {
				$this->sstrs[] = trim($s);
			}
		} else {
			$this->sstrs[] = $sterm;
		}

		$userID = $this->instanceObj->uid;

		$db = $this->getDbo();
		$db->getConnection()->sqliteCreateFunction('sfunc', [$this,'sfunc'], 1);

		$query = $db->getQuery(true);
		$query->select('I.itemID,I.title,I.isParent,I.shared,I.secured,I.vtotal,I.vcount')->from('notes AS I');
		if ((int)JVERSION < 4) {
			$query->innerJoin('content AS C ON C.contentID=I.contentID');
		} else {
			$query->innerJoin('content AS C','C.contentID=I.contentID');
		}
		$query->where(['I.secured IS NOT 1','(I.ownerID == \''.$userID.'\' OR I.shared)']);
		$query->andWhere(['sfunc(C.serial_content)','sfunc(I.title)']);
		$db->setQuery($query);
		$a1 = $db->loadObjectList();

		// also check secured note titles (since they are encoded)
		$query->clear()->select('I.itemID,I.title,I.isParent,I.shared,I.secured,I.vtotal,I.vcount')->from('notes AS I');
		$query->where(['I.secured IS 1','(I.ownerID == \''.$userID.'\' OR I.shared)']);
		$query->andWhere('sfunc(b64d(I.title))');
		$db->setQuery($query);
		$a2 = $db->loadObjectList();
		return array_merge($a1, $a2);
	}


	public function addItemPaths (&$items)
	{
		$db = $this->getDbo();
		foreach ($items as &$item) {
			$to = $item->itemID;
			$path = [];
			while ($to) {
				$db->setQuery('SELECT title,parentID,secured FROM notes WHERE itemID='.$to);
				$r = $db->loadAssoc();
				if ($r['secured']) {
					$r['title'] = base64_decode($r['title']);
				}
				array_unshift($path, $r['title']);
				$to = $r['parentID'];
			}
			$item->lPath = implode(' :: ', $path);
		}
	}


	public function getItem ($iid=null)
	{
		$iid = (!empty($iid)) ? $iid : (int) $this->getState('parent.id');
		if (!$iid) return false;
		$db = $this->getDbo();
		$db->setQuery('SELECT * FROM notes WHERE itemID == '.$iid);
		$data = $db->loadObject();
		return $data;
	}


	public function moveItem ($iid, $pid)
	{
		$db = $this->getDbo();
		$db->setQuery('UPDATE notes SET parentID = '.$pid.' WHERE itemID == '.$iid);
		$db->execute();
		return '';
	}


	private function buildBranch ($id, $ind, &$rows, &$tree)
	{
		foreach ($rows as $row) {
			if ($row->parentID == $id) {
				if ($row->secured) { $row->title = base64_decode($row->title); }
				$tree[$row->itemID] = $ind.UserNotesHelper::fs_db($row->title);
				$this->buildBranch($row->itemID, $ind.'-&nbsp;', $rows, $tree);
			}
		}
	}


	public function get_item_hier ($userID=0)
	{
		$db = $this->getDbo();
		$db->setQuery('SELECT * FROM notes WHERE isParent == 1 AND (ownerID == '.$userID.' OR shared) ORDER BY parentID,title');
		$rows = $db->loadObjectList();
		$hier = [0 => '&lt;'.'My Notes'.'&gt;'];
		$this->buildBranch(0, '-&nbsp;', $rows, $hier);
		return $hier;
	}


	// get storage useage in bytes
	public function getStorSize ()
	{
		// get the DB file size
		$dbsz = filesize($this->_storPath.self::DBFILE);

		// get total of attachment sizes
		$db = $this->getDbo();
		$atsz = $db->setQuery('SELECT totatt FROM attsizsum')->loadResult();

		return $dbsz + $atsz;
	}


	protected function getListQuery ()
	{
		$pid = $this->getState('parent.id') ? : 0;
		$db = $this->getDbo();
		$query = $db->getQuery(true);
		$query->select('*')->from('notes')->where('parentID='.$pid);
		if ($this->getState('hide-secure')) $query->where('secured IS NOT 1');
		return $query;
	}


	protected function populateState ($ordering = null, $direction = null)
	{
		// Initialize variables
		$app = Factory::getApplication();
		$params = JComponentHelper::getParams('com_usernotes');
		$input = $app->input;

		// menu params
		$mparams = $app->getParams();
		$this->setState('secured', (bool)$mparams->get('secured', false));

		// album ID
		$pid = $input->get('pid', 0, 'INT');
		$this->state->set('parent.id', $pid);

		// List state information
		$limit = $app->getUserStateFromRequest('global.list.limit', 'limit', $app->getCfg('list_limit'));
		$this->setState('list.limit'.$pid, $limit);

		$limitstart = $input->getInt('limitstart', 0);
		$this->setState('list.start'.$pid, $limitstart);

		// Load the parameters.
		$this->setState('cparams', $params);

		// set whether secured item should be shown
		$userID = $this->instanceObj->uid;
		$this->setState('hide-secure', !(bool)$userID);
	}

}
