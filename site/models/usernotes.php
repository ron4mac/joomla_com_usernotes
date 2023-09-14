<?php
/**
* @package		com_usernotes
* @copyright	Copyright (C) 2015-2023 RJCreations. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
* @since		1.3.4
*/
defined('_JEXEC') or die;

use Joomla\CMS\Factory;

class UserNotesModelUserNotes extends Joomla\CMS\MVC\Model\ListModel
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
		$db->getConnection()->sqliteCreateFunction('sfunc', [$this,'sfunc'], 1);
		$db->getConnection()->sqliteCreateFunction('vavg', [$this,'vavg'], 2);

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
	// an sqlite extended function to get the average vote (rating)
	public function vavg ($tot, $cnt) {
		return $tot ? ($tot/$cnt) : 0;
	}


	public function search ($sterm, $pid)
	{
		if ($sterm == '**starred') return $this->starred($pid);
		if ($sterm == '**recent') return $this->recent($pid);

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
//		$db->getConnection()->sqliteCreateFunction('sfunc', [$this,'sfunc'], 1);

		$query = $db->getQuery(true);
		$query->select('I.itemID,I.title,I.isParent,I.parentID,I.shared,I.secured,I.vtotal,I.vcount')->from('notes AS I');
		if ((int)JVERSION < 4) {
			$query->innerJoin('content AS C ON C.contentID=I.contentID');
		} else {
			$query->innerJoin('content AS C','C.contentID=I.contentID');
		}
		$query->where(['I.secured IS NOT 1','(I.ownerID == \''.$userID.'\' OR I.shared)']);
		$query->andWhere(['sfunc(C.serial_content)','sfunc(I.title)']);
		$db->setQuery($query);
		$this->logQ($db);
		$a1 = $db->loadObjectList();

		// also check secured note titles (since they are encoded)
		$query->clear()->select('I.itemID,I.title,I.isParent,I.parentID,I.shared,I.secured,I.vtotal,I.vcount')
			->from('notes AS I')
			->where(['I.secured IS 2','(I.ownerID == \''.$userID.'\' OR I.shared)'])
			->andWhere('sfunc(b64d(I.title))');
		$db->setQuery($query);
		$this->logQ($db);
		$a2 = $db->loadObjectList();
		return array_merge($a1, $a2);
	}


	private function starred ($pid)
	{
		$userID = $this->instanceObj->uid;
		$db = $this->getDbo();
		$query = $db->getQuery(true);
		$query->select('I.itemID,I.title,I.isParent,I.parentID,I.shared,I.secured,I.vtotal,I.vcount')
			->from('notes AS I')
			->where('I.vcount > 0')
			->andWhere(['I.secured IS 0','I.ownerID == '.$userID,'I.shared'], 'OR')
			->order('vavg(I.vtotal,I.vcount) DESC');
		$db->setQuery($query);
		$this->logQ($db);
		$lst = $db->loadObjectList();
		return $lst;
	}


	private function recent ($pid)
	{
		$userID = $this->instanceObj->uid;
		$db = $this->getDbo();
		$query = $db->getQuery(true);
		$query->select('I.itemID,I.title,I.isParent,I.parentID,I.shared,I.secured,I.vtotal,I.vcount')
			->from('notes AS I')
			->where('I.isParent IS NOT 1')
			->andWhere('I.cdate IS NOT NULL')
			->andWhere(['I.secured IS 0','I.ownerID == '.$userID,'I.shared'], 'OR')
			->order('I.cdate DESC');
		$db->setQuery($query, 0, 20);
		$this->logQ($db);
		$lst = $db->loadObjectList();
		return $lst;
	}


	public function addItemPaths (&$items)
	{
		static $C = [];	// cache parent items

		$db = $this->getDbo();
		foreach ($items as &$item) {
			$pid = $item->parentID;
			$path = [$item->secured ? base64_decode($item->title) : $item->title];
			while ($pid) {
				if (isset($C[$pid])) {
					$r = $C[$pid];
				} else {
					$db->setQuery('SELECT title,parentID,secured FROM notes WHERE itemID='.$pid);
					$this->logQ($db);
					$r = $db->loadAssoc();
					if ($r['secured']) {
						$r['title'] = base64_decode($r['title']);
					}
					$C[$pid] = $r;
				}
				array_unshift($path, $r['title']);
				$pid = $r['parentID'];
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
		$this->logQ($db);
		$data = $db->loadObject();
		return $data;
	}


	public function moveItem ($iid, $pid)
	{
		$db = $this->getDbo();
		$db->setQuery('UPDATE notes SET parentID = '.$pid.' WHERE itemID == '.$iid);
		$this->logQ($db);
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
		$this->logQ($db);
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
		$query->order('title COLLATE NOCASE');
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


	private function logQ ($db)
	{
		$q = (string)$db->getQuery();
		file_put_contents('QLOG.txt', $q."\n", FILE_APPEND);
	}

}
