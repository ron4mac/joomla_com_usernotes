<?php
/**
 * @package    com_usernotes
 *
 * @copyright  Copyright (C) 2016-2019 RJCreations - All rights reserved.
 * @license    GNU General Public License version 3 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

JLoader::register('UserNotesHelper', JPATH_COMPONENT_ADMINISTRATOR.'/helpers/usernotes.php');

class UserNotesModelUserNotes extends JModelList
{
	protected $_storPath = null;

	public function __construct ($config=array())
	{
		$this->_storPath = UserNotesHelper::userDataPath();
		$udbPath = $this->_storPath.'/usernotes.db3';
		$doInit = !file_exists($udbPath);
		
		$option = array('driver'=>'sqlite', 'database'=>$udbPath);
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


	public function search ($sterm)
	{
		$db = $this->getDbo();
		$userID = JFactory::getUser()->get('id');
		$db->setQuery("SELECT I.itemID,I.title,I.isParent,I.shared,I.secured FROM notes AS I JOIN content AS C ON C.contentID=I.contentID "
			."WHERE I.secured IS NOT 1 AND (I.ownerID == '".$userID."' OR I.shared) AND (C.serial_content LIKE \"%".$sterm."%\" OR I.title LIKE \"%".$sterm."%\")");
		$a1 = $db->loadObjectList();
		$db->setQuery("SELECT I.itemID,I.title,I.isParent,I.shared,I.secured FROM notes AS I "
			."WHERE I.secured IS 1 AND (I.ownerID == '".$userID."' OR I.shared) AND (b64d(I.title) LIKE \"%".$sterm."%\")");
		$a2 = $db->loadObjectList();
		return array_merge($a1, $a2);
	}


	public function addItemPaths (&$items)
	{
		$db = $this->getDbo();
		foreach ($items as &$item) {
			$to = $item->itemID;
			$path = array();
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
		$hier = array(0=>'&lt;'.'My Notes'.'&gt;');
		$this->buildBranch(0, '-&nbsp;', $rows, $hier);
		return $hier;
	}


	// get storage useage in bytes
	public function getStorSize ()
	{
		// get the DB file size
		$dbsz = filesize($this->_storPath.'/usernotes.db3');

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
		return $query;
	}


	protected function populateState ($ordering = null, $direction = null)
	{
		// Initialize variables
		$app = JFactory::getApplication();
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
	}

}
