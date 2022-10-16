<?php
/**
* @package		com_usernotes
* @copyright	Copyright (C) 2015-2022 RJCreations. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
*/
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\ItemModel;

JLoader::register('UserNotesHelper', JPATH_COMPONENT_ADMINISTRATOR.'/helpers/usernotes.php');

class UserNotesModelUserNote extends ItemModel
{
	const DBFILE = '/usernotes.db3';
	protected $_context = 'com_usernotes.usernote';
	protected $_storPath = null;
	protected $_item = null;	// use for cache


	public function __construct ($config = [])
	{
		$this->_storPath = UserNotesHelper::userDataPath();
		$udbPath = $this->_storPath.self::DBFILE;
		$db = JDatabaseDriver::getInstance(['driver'=>'sqlite', 'database'=>$udbPath]);

		$config['dbo'] = $db;
		parent::__construct($config);
	}


	public function &getItem ($pk = null)
	{
		$pk = (!empty($pk)) ? $pk : (int) $this->getState('usernote.id');

		if ($this->_item === null) {
			$this->_item = [];
		}

		if (!isset($this->_item[$pk])) {
			try
			{
				$db = $this->getDbo();
				$query = $db->getQuery(true)
					->select('n.*, c.serial_content'/*, a.attached'*/)
					->from('notes AS n')
					->join('LEFT', 'content AS c on c.contentID = n.contentID')
					->where('n.itemID = ' . (int)$pk);

				$db->setQuery($query);

				$data = $db->loadObject();

				if (empty($data)) {
					throw new Exception(Text::_('COM_USERNOTES_ERROR_NOTE_NOT_FOUND'), 404);
				} else {
					if ($nm = @unserialize($data->serial_content)) {
						$data->serial_content = $nm->rendered();
					}
					$data->attached = $this->attachments($data->contentID);
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


	public function addRating ($iid, $rate)
	{
		try
		{
			$db = $this->getDbo();
			$db->transactionStart();
			if ($rate) {
				$db->setQuery('SELECT vcount,vtotal FROM notes WHERE itemID='.$iid);
				$data = $db->loadRow();
				$data[0]++; $data[1] += $rate;
			} else {
				$data = [0,0];
			}
			$db->setQuery('UPDATE notes SET vcount='.$data[0].' ,vtotal='.$data[1].' WHERE itemID='.$iid);
			$db->execute();
			$db->transactionCommit();
			return ($data[0] ? $data[1]/$data[0] : 0).':('.$data[0].')';
		}
		catch (Exception $e)
		{
			$this->setError($e);
		}
	}


	// save a new or edited folder item
	// @data [itemID, title, ephrase, contentID, serial_content, parentID, maksec, pissec]
	// @user user id#
	public function storeNote (JInput $data, $user)
	{
		$iid = $data->getInt('itemID');
		$secured = 0;
		$ephrase = $data->getString('ephrase', null);
		$ntitl = trim($data->getString('title'));
		$ncont = JComponentHelper::filterText($data->getRaw('serial_content'));
		if ($ephrase) {
			$secured = 2;	// use OpenSSL
			$ntitl = base64_encode($ntitl);
			$ncont = UserNotesHelper::doCrypt($ephrase, $ncont);
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
				$q->update('notes')->set('title='.$db->quote($ntitl).', secured='.$secured)->where('itemID='.$iid);
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
								->values(implode(',', [$user, 1, 0, $db->quote($ntitl), $cid, $data->getInt('parentID'), $secured]));
				$db->setQuery($q);
				$db->execute();
			}
		}
		catch (Exception $e)
		{
			$this->setError($e);
		}
	}

	// save a new or edited folder item
	// @data [itemID, title, parentID, maksec, pissec]
	// @user user id#
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
				if ($data->getInt('pissec',0)) {
					$ftitl = base64_encode($ftitl);
				}
				$q = $db->getQuery(true);
				$q->update('notes')->set('title='.$db->quote($ftitl))->where('itemID='.$iid);
				$db->setQuery($q);
				$db->execute();
				$pid = $iid;
			} else {
				$fpid = $data->getInt('parentID');
				if ($data->getInt('maksec',0) || $data->getInt('pissec',0)) {
					$sec = 1;
					$ftitl = base64_encode($ftitl);
				}
				$q = $db->getQuery(true);
				$q->insert('notes')->columns('ownerID,shared,isParent,title,contentID,parentID,secured')
									->values(implode(',', [$user, 1, 1, $db->quote($ftitl), 0, $fpid, $sec]));
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


	public function getForm ($data = [], $loadData = true)
	{
		// Get the encryption phrase form.
		$form = JForm::getInstance('com_usernotes.ephrase', JPATH_COMPONENT.'/models/forms/ephrase.xml');

		if (empty($form)) {
			return false;
		}

		return $form;
	}


	public function add_attached ($contentID=0, $files=NULL)
	{
		if (!$contentID || !$files) return;
		$path = JPATH_BASE.'/'.UserNotesHelper::userDataPath().'/attach/'.$contentID;
		$msg = '';
		$fns = [];
		foreach ($files as $file) {
			if ($file['error'] == UPLOAD_ERR_OK) {
				$tmp_name = $file['tmp_name'];
				if (is_uploaded_file($tmp_name)) {
					@mkdir($path);
					$name = $file['name'];
					move_uploaded_file($tmp_name, $path.'/'.$name);
					$fns[] = $name;
				}
				else $msg .= Text::_('COM_USERNOTES_NOUPLOAD');
			}
			elseif ($file['error'] != UPLOAD_ERR_NO_FILE) {
				$msg .= sprintf(Text::_('COM_USERNOTES_UPLOADERR'), $file['error']);
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
				$msg = $e->getMessage();
			}
		}
		return $msg;
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


	public function renameAttachment ($contentID=0, $file=null, $tofile=null)
	{
		if (!$contentID || !$file || !$tofile) return;
		if ($file == $tofile) return;
		$path = JPATH_BASE.'/'.UserNotesHelper::userDataPath().'/attach/'.$contentID.'/';
		if (!file_exists($path.$file)) return 'No such file';
		if (file_exists($path.$tofile)) return 'File already exists';
		if (!rename($path.$file,$path.$tofile)) return 'Failed to rename file';
		try
		{
			$db = $this->getDbo();
			$db->setQuery('UPDATE fileatt SET attached='.$db->quote($tofile).' WHERE contentID='.$contentID.' AND attached='.$db->quote($file));
			$db->execute();
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

		$atDir = $this->_storPath.'/attach/'.$contentID;

		// do nothing if there are no attachments
		if (!file_exists($atDir)) return;

		$atts = [];
		try
		{
			$db = $this->getDbo();
			$db->setQuery('SELECT contentID,attached FROM fileatt WHERE contentID='.$contentID);
			$atts = $db->loadRowList();
			foreach ($atts as $att) {
				unlink($atDir.'/'.$att[1]);
			}
			@unlink($atDir.'/index.html');
			@rmdir($atDir);
			$db->setQuery('DELETE FROM fileatt WHERE contentID='.$contentID);
			$db->execute();
		}
		catch (Exception $e)
		{
			$this->setError($e);
		}
		return false;
	}


	public function dofraction ($cid)
	{
		try
		{
			$db = $this->getDbo();
			$db->setQuery('SELECT serial_content FROM content WHERE contentID='.$cid);
			$cont = $db->loadResult();
			$pattern = '/([^\d])(\d)\/(\d)([^\d])/';
			$replacement = '$1&frac$2$3;$4';
			$nutxt = preg_replace($pattern, $replacement, $cont);
			$pattern = '/([^\d])(\d) (&frac)/';
			$replacement = '$1$2$3';
			$nutxt = preg_replace($pattern, $replacement, $nutxt);
			$db->setQuery('UPDATE content SET serial_content='.$db->quote($nutxt).' WHERE contentID='.$cid);
			$db->execute();
		}
		catch (Exception $e)
		{
			$this->setError($e);
		}
	}


	public function unfraction ($cid)
	{
		try
		{
			$db = $this->getDbo();
			$db->setQuery('SELECT serial_content FROM content WHERE contentID='.$cid);
			$cont = $db->loadResult();
			$pattern = '/([^\d])(\d)(&frac)/';
			$replacement = '$1$2 $3';
			$nutxt = preg_replace($pattern, $replacement, $cont);
			$pattern = '/&frac(\d)(\d);/';
			$replacement = '$1/$2';
			$nutxt = preg_replace($pattern, $replacement, $nutxt);
			$db->setQuery('UPDATE content SET serial_content='.$db->quote($nutxt).' WHERE contentID='.$cid);
			$db->execute();
		}
		catch (Exception $e)
		{
			$this->setError($e);
		}
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
		$app = Factory::getApplication();
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