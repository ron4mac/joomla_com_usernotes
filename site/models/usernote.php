<?php
/**
 * @package    com_usernotes
 *
 * @copyright  Copyright (C) 2016 RJCreations - All rights reserved.
 * @license    GNU General Public License version 3 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

include_once JPATH_COMPONENT.'/classes/note_class.php';

JLoader::register('UserNotesHelper', JPATH_COMPONENT_ADMINISTRATOR.'/helpers/usernotes.php');

class UserNotesModelUserNote extends JModelItem
{
	protected $_context = 'com_usernotes.usernote';
	protected $_storPath = null;
	protected $_item = null;	// use for cache

	public function __construct ($config = array())
	{
		$this->_storPath = UserNotesHelper::userDataPath();
		$udbPath = $this->_storPath.'/usernotes.db3';
		$db = JDatabaseDriver::getInstance(array('driver'=>'sqlite', 'database'=>$udbPath));

		$config['dbo'] = $db;
		parent::__construct($config);
	}


	public function &getItem ($pk = null)
	{
		$pk = (!empty($pk)) ? $pk : (int) $this->getState('usernote.id');

		if ($this->_item === null) {
			$this->_item = array();
		}

		if (!isset($this->_item[$pk])) {
			try
			{
				$db = $this->getDbo();
				$query = $db->getQuery(true)
					->select('n.*, c.serial_content'/*, a.attached'*/)
					->from('notes AS n')
					->join('LEFT', 'content AS c on c.contentID = n.contentID')
					->where('n.itemID = ' . (int) $pk);

				$db->setQuery($query);

				$data = $db->loadObject();

				if (empty($data)) {
					JError::raiseError(404, JText::_('COM_USERNOTES_ERROR_FEED_NOT_FOUND'));
				} else {
					if ($nm = @unserialize($data->serial_content)) {
						$data->serial_content = $nm->rendered();
					}
					$db->setQuery('SELECT attached FROM fileatt WHERE contentID='.$data->contentID);
					$data->attached = $db->loadRowList();
				}

				if ($data->secured) {
					$data->title = base64_decode($data->title);
				}
				$this->_item[$pk] = $data;
			}
			catch (Exception $e)
			{
				$this->setError($e);
				$this->_item[$pk] = false;
			}
		}

		return $this->_item[$pk];
	}


	public function storeNote (JInput $data, $user)
	{
		$iid = $data->getInt('itemID');
		$secured = 0;
		$ephrase = $data->getString('ephrase', null);
		$ntitl = trim($data->getString('title'));
		$ncont = JComponentHelper::filterText($data->getRaw('serial_content'));
		if ($ephrase) {
			$secured = 1;
			$ntitl = base64_encode($ntitl);
			$ncont = base64_encode(UserNotesHelper::doCrypt($ephrase, $ncont));
		}

		try
		{
			$db = $this->getDbo();
			if ($iid) {
				$q = $db->getQuery(true);
				$q->update('content')->set('serial_content='.$db->quote($ncont))->where('contentID='.$data->getInt('contentID'));
				$db->setQuery($q);
				$db->execute();
				$q = $db->getQuery(true);
				$q->update('notes')->set('title='.$db->quote($ntitl))->where('itemID='.$iid);
				$db->setQuery($q);
				$db->execute();
			} else {
				$q = $db->getQuery(true);
				$q->insert('content')->columns('serial_content')->values($db->quote($ncont));
				$db->setQuery($q);
				$db->execute();
				$cid = $db->insertid();
				$q = $db->getQuery(true);
				$q->insert('notes')->columns('ownerID,shared,isParent,title,contentID,parentID,secured')
								->values(implode(',',array($user,1,0,$db->quote($ntitl),$cid,$data->getInt('parentID'),$secured)));
				$db->setQuery($q);
				$db->execute();
			}
		}
		catch (Exception $e)
		{
			$this->setError($e);
		}
	}


	public function storeFolder ($data, $user)
	{
		$iid = $data->getInt('itemID');
		$ftitl = trim($data->getString('title'));
		$sec = 0;
		$pid = 0;
		try
		{
			$db = $this->getDbo();
			if ($iid) {
				$q = $db->getQuery(true);
				$q->update('notes')->set('title='.$db->quote($ftitl))->where('itemID='.$iid);
				$db->setQuery($q);
				$db->execute();
				$pid = $iid;
			} else {
				$fpid = $data->getInt('parentID');
				if ($data->getInt('maksec',0) || $data->getInt('pissec',0)) {
					$sec = 1;
					$ttl = base64_encode($ftitl);
				}
				$q = $db->getQuery(true);
				$q->insert('notes')->columns('ownerID,shared,isParent,title,contentID,parentID,secured')
									->values(implode(',',array($user,1,1,$db->quote($ftitl),0,$fpid,$sec)));
				$db->setQuery($q);
				$db->execute();
				$pid = $db->insertid();
			}
		}
		catch (Exception $e)
		{
			$this->setError($e);
		}
		return $pid;
	}


	public function add_attached ($contentID=0, $files=NULL, $notesid=null)
	{
		if (!$contentID || !$files) return;
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
			try
			{
				$db = $this->getDbo();
				foreach ($fns as $fn) {
					$fsz = filesize($path.'/'.$fn);
					$db->setQuery('SELECT attached FROM fileatt WHERE contentID='.$contentID.' AND attached='.$db->quote($fn));
					$r = $db->loadResult();
					if ($r) {
						$db->setQuery('UPDATE fileatt SET fsize='.$fsz.' WHERE contentID='.$contentID.' AND attached='.$db->quote($fn));
						$db->execute();
					} else {
						$db->setQuery('INSERT INTO fileatt (contentID,fsize,attached) VALUES ('.$contentID.','.$fsz.','.$db->quote($fn).')');
						$db->execute();
					}
				}
			}
			catch (Exception $e)
			{
				$this->setError($e);
			}
		}
		if ($msg) { $this->setError("[cid:{$contentID}] " . $msg); }
	}


	public function attachments ($contentID=0)
	{
		if (!$contentID) return false;
		try
		{
			$db = $this->getDbo();
			$db->setQuery('SELECT attached FROM fileatt WHERE contentID='.$contentID);
			return $db->loadRowList();
		}
		catch (Exception $e)
		{
			$this->setError($e);
		}
	}


	public function deleteAttachment ($contentID=0, $file=null)
	{
		if (!$contentID || !$file) return;
		try
		{
			$db = $this->getDbo();
			$q = $db->getQuery(true);
			$q->delete('fileatt')
				->where('contentID='.$contentID)
				->where('attached='.$db->quote($file));
			$db->setQuery($q)->execute();
			unlink($this->_storPath.'/attach/'.$contentID.'/'.$file);
		}
		catch (Exception $e)
		{
			$this->setError($e);
		}
		return false;
	}


	public function deleteAttachments ($contentID=0)
	{
		if (!$contentID) return;
		$atts = array();
		try
		{
			$db = $this->getDbo();
			$db->setQuery('SELECT contentID,attached FROM fileatt WHERE contentID='.$contentID);
			$atts = $db->loadRowList();
			foreach ($atts as $att) {
				unlink($this->_storPath.'/attach/'.$contentID.'/'.$att[1]);
			}
			@unlink($this->_storPath.'/attach/'.$contentID.'/index.html');
			rmdir($this->_storPath.'/attach/'.$contentID);
			$db->setQuery('DELETE FROM fileatt WHERE contentID='.$contentID);
			$db->execute();
		}
		catch (Exception $e)
		{
			$this->setError($e);
		}
		return false;
	}


	public function deleteItem ($iid)
	{
		try
		{
			$db = $this->getDbo();
			$q = $db->getQuery(true);
			$q->select('contentID,isParent,parentID')->from('notes')->where('itemID='.$iid);
			$db->setQuery($q);
			$itm = $db->loadObject();
			if ($itm->isParent) {
				$this->deleteFolder($iid);
			} else {
				$q = $db->getQuery(true);
				$q->delete('content')->where('contentID='.$itm->contentID);
				$db->setQuery($q);
				$db->execute();
				$this->deleteAttachments($itm->contentID);
			}
			$db->setQuery('DELETE FROM notes WHERE itemID='.$iid);
			$db->execute();
			return $itm->parentID;
		}
		catch (Exception $e)
		{
			$this->setError($e);
		}
		return false;
	}


	private function deleteFolder ($iid)
	{
		try
		{
			$db = $this->getDbo();
			$db->setQuery('SELECT itemID FROM notes WHERE parentID='.$iid);
			$itms = $db->loadAssocList();
			foreach ($itms as $itm) {
				$this->deleteItem($itm['itemID']);
			}
		}
		catch (Exception $e)
		{
			$this->setError($e);
		}
	}


	protected function populateState ()
	{
		// Initialize variables
		$app = JFactory::getApplication();
		$params = JComponentHelper::getParams('com_usernotes');
		$input = $app->input;

		// menu params
		$mparams = $app->getParams();
		$this->state->set('secured', (bool)$mparams->get('secured', false));

		// album ID
		$nid = $input->get('nid', 0, 'INT');
		$this->state->set('usernote.id', $nid);

		// Load the parameters.
		$this->setState('params', $params);
	}

}