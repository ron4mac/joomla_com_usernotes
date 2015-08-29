<?php
defined('_JEXEC') or die;

use Joomla\Registry\Registry;

JLoader::register('UserNotesHelper', JPATH_COMPONENT_ADMINISTRATOR.'/helpers/usernotes.php');

class UserNotesModelUserNotes extends JModelList
{
	public function __construct ($config = array())
	{
		$dbFile = '/usernotes.db3';
		$udbPath = UserNotesHelper::userDataPath().$dbFile;
		$doInit = !file_exists($udbPath);
		
		$option = array('driver'=>'sqlite', 'database'=>$udbPath);

		$db = JDatabaseDriver::getInstance($option);

		if ($doInit) {
			require_once JPATH_COMPONENT.'/helpers/db.php';
			UserNotesHelperDb::buildDb($db);
		}

		$config['dbo'] = $db;
		parent::__construct($config);
	}
/*
	public function getTitle ()
	{
		$pid = $this->getState('parent.id') ? : 0;
	//	$db = parent::getDBO();
		$db = $this->getDbo();
		$db->setQuery('SELECT title FROM notes WHERE itemID='.$pid);
		return $db->loadResult();
	}
*/

	public function search ($sterm)
	{
	//	$db = parent::getDBO();
		$db = $this->getDbo();
		$userID = JFactory::getUser()->get('id');
		$db->setQuery("SELECT I.itemID,I.title,I.isParent,I.shared FROM notes AS I JOIN content AS C ON C.contentID=I.contentID "
			."WHERE (I.ownerID == '".$userID."' OR I.shared) AND (C.serial_content LIKE \"%".$sterm."%\" OR I.title LIKE \"%".$sterm."%\")");
		return $db->loadObjectList();
	}

	public function buildPathway ($to)
	{
		$pw = JFactory::getApplication()->getPathWay();
	//	$db = parent::getDBO();
		$db = $this->getDbo();
		$crums = array();
		while ($to) {
			$db->setQuery('SELECT title,parentID FROM notes WHERE itemID='.$to);
			$r = $db->loadAssoc();
			array_unshift($crums, array($r['title'],'index.php?option=com_usernotes&pid='.$to));
			$to = $r['parentID'];
		}
		foreach ($crums as $crum) {
			$pw->addItem($crum[0],$crum[1]);
		}
	}

	public function getItem ($iid=null)
	{
		$iid = (!empty($iid)) ? $iid : (int) $this->getState('parent.id');
		if (!$iid) return false;
	//	$db = parent::getDBO();
		$db = $this->getDbo();
		$db->setQuery('SELECT * FROM notes WHERE itemID == '.$iid);
		$data = $db->loadObject();
		return $data;
	}

	public function moveItem ($iid, $pid)
	{
	//	$db = parent::getDBO();
		$db = $this->getDbo();
		$db->setQuery('UPDATE notes SET parentID = '.$pid.' WHERE itemID == '.$iid);
		$db->execute();
		return '';
	}

	private function buildBranch ($id,$ind,&$rows,&$tree)
	{
		foreach ($rows as $row) {
			if ($row->parentID == $id) {
				$tree[$row->itemID] = $ind.UserNotesHelper::fs_db($row->title);
				$this->buildBranch($row->itemID,$ind.'-&nbsp;',$rows,$tree);
			}
		}
	}
	
	public function get_item_hier ($userID=0)
	{
	//	$db = parent::getDBO();
		$db = $this->getDbo();
		$db->setQuery('SELECT * FROM notes WHERE isParent == 1 AND (ownerID == '.$userID.' OR shared) ORDER BY parentID,title');
		$rows = $db->loadObjectList();
		$hier = array(0=>'&lt;'.'My Notes'.'&gt;');
		$this->buildBranch(0,'-&nbsp;',$rows,$hier);
		return $hier;
	}

	public function attachments ($contentID=0)
	{
	//	$db = parent::getDBO();
		$db = $this->getDbo();
		$db->transactionStart();
		$db->setQuery('SELECT attached FROM attach WHERE contentID == '.$contentID);
		$r = $db->loadResult();
		return unserialize($r);
	}

	public function add_attached ($contentID=0, $files=NULL, $notesid=null)
	{
		if (!$contentID) return;
		if (!$files) return;
		$path = JPATH_BASE.'/'.UserNotesHelper::userDataPath().'/attach/'.$contentID;
		$msg = '';
		$fns = array();
		foreach ($files as $file) {
			if ($file['error'] == UPLOAD_ERR_OK) {
				$tmp_name = $file['tmp_name'];
				if (is_uploaded_file($tmp_name)) {
					@mkdir($path);
					$name = $file['name'];
					move_uploaded_file($tmp_name, $path.'/'.$name);
					$fns[] = $name;
				}
				else $msg .= 'failed to upload';
			}
			elseif ($file['error'] != UPLOAD_ERR_NO_FILE) {
				$msg .= "Error: {$file['error']}";
			}
		}
		if ($fns) {
	//		$db = parent::getDBO();
			$db = $this->getDbo();
			$db->transactionStart();
			$db->setQuery('SELECT attached FROM attach WHERE contentID == '.$contentID);
			$r = $db->loadObject();
			if ($r) {
				$atch = unserialize($r->attached);
				foreach ($fns as $fn) {
					if (in_array($fn,$atch)) continue;
					$atch[] = $fn;
				}
				$db->setQuery('UPDATE attach SET attached = '.$db->quote(serialize($atch)).' WHERE contentID == '.$contentID);
				$db->execute();
			} else {
				$db->setQuery('INSERT INTO attach (contentID,attached) VALUES ('.$contentID.','.$db->quote(serialize($fns)).')');
				$db->execute();
			}
			$db->transactionCommit();
		}
		if ($msg) { var_dump($contentID,$msg); }
	}

	public function del_attached ($contentID=0, $file=null)
	{
		if (!$contentID) return;
		if (!$file) return;
	//	$db = parent::getDBO();
		$db = $this->getDbo();
		$db->transactionStart();
		$db->setQuery('SELECT attached FROM attach WHERE contentID == '.$contentID);
		$r = $db->loadObject();
		if ($r) {
			$atchs = unserialize($r->attached);
			foreach ($atchs as $k=>$atch) {
				if ($atch == $file) {
					unset($atchs[$k]);
					break;
				}
			}
			$db->setQuery('UPDATE attach SET attached = '.$db->quote(serialize($atchs)).' WHERE contentID == '.$contentID);
			$db->execute();
		} else {
			return 'No attachments found';
		}
		$db->transactionCommit();
		return false;
	}

	protected function getListQuery ()
	{
		$pid = $this->getState('parent.id') ? : 0;
	//	$db = parent::getDBO();
		$db = $this->getDbo();
		$query = $db->getQuery(true);

		$query->select('*');
		$query->from('notes');
		$query->where('parentID='.$pid);

		return $query;
	}

	protected function populateState ($ordering = null, $direction = null)
	{
		// Initialize variables
		$app = JFactory::getApplication();
		$params = JComponentHelper::getParams('com_usernotes');
		$input = $app->input;

		// album ID
		$pid = $input->get('pid', 0, 'INT');
		$this->state->set('parent.id', $pid);

		// List state information
		$limit = $app->getUserStateFromRequest('global.list.limit', 'limit', $app->getCfg('list_limit'));
		$this->setState('list.limit'.$pid, $limit);

		$limitstart = $input->getInt('limitstart', 0);
		$this->setState('list.start'.$pid, $limitstart);

		// Load the parameters.
		$this->setState('params', $params);
	}
}