<?php
/**
 * @package    com_usernotes
 * @copyright  Copyright (C) 2016-2021 RJCreations - All rights reserved.
 * @license    GNU General Public License version 3 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

JLoader::register('UserNotesHelper', JPATH_COMPONENT_ADMINISTRATOR.'/helpers/usernotes.php');

class UserNotesModelEdit extends JModelForm
{
	const DBFILE = '/usernotes.db3';
	protected $_context = 'com_usernotes.usernote';
	protected $_data;


	public function __construct ($config = [])
	{
		$udbPath = UserNotesHelper::userDataPath().self::DBFILE;
		$db = JDatabaseDriver::getInstance(['driver'=>'sqlite', 'database'=>$udbPath]);

		$config['dbo'] = $db;
		parent::__construct($config);
	}


	public function getItem ($nid=null)
	{
		$nid = (!empty($nid)) ? $nid : (int) $this->getState('usernote.id');
		if (!$nid) return null;
		$data = null;
		try
		{
			$db = $this->getDbo();
			$query = $db->getQuery(true)
				->select('n.*, c.serial_content'/*, a.attached'*/)
				->from('notes AS n')
				->join('LEFT', 'content AS c on c.contentID = n.contentID')
				->where('n.itemID = ' . (int) $nid);

			$db->setQuery($query);

			$data = $db->loadObject();

			if (empty($data)) {
				JError::raiseError(404, Text::_('COM_USERNOTES_ERROR_NOTE_NOT_FOUND'));
			} else {
				if ($data->serial_content) {
					if ($nm = @unserialize($data->serial_content)) {
						$data->serial_content = $nm->rendered();
					}
				}
				if ($data->contentID) {
					$db->setQuery('SELECT attached FROM fileatt WHERE contentID='.$data->contentID);
					$data->attached = $db->loadRowList();
				}
			}
		}
		catch (JDatabaseExceptionExecuting $e)
		{
			$this->setError($e->getQuery());
		}
		return $data;
	}


	public function checkOut ($nid=null)
	{
		if (!$nid) return true;
		$uid = Factory::getUser()->get('id');
		if (!$uid) return false;
		$db = $this->getDbo();
		$db->setQuery('UPDATE notes SET checked_out = '.$uid.', checked_out_time = '.time().' WHERE itemID == '.$nid);
		$db->execute();
	}


	public function checkIn ($nid=null)
	{
		if (!$nid) return true;
		$uid = Factory::getUser()->get('id');
		if (!$uid) return false;
		$db = $this->getDbo();
		$db->setQuery('UPDATE notes SET checked_out = 0, checked_out_time = NULL WHERE itemID == '.$nid);
		$db->execute();
	}


	public function itemIsSecure ($nid)
	{
		if (!$nid) return false;
		$db = $this->getDbo();
		$db->setQuery('SELECT secured FROM notes WHERE itemID='.$nid);
		return $db->loadResult();
	}


	public function getForm ($data = [], $loadData = true)
	{
		// Initialize variables
		$app = Factory::getApplication();
		$input = $app->input;

		if ($input->get('type','','cmd') == 'f') {
			$src = 'com_usernotes.fold';
			$nam = 'fold';
		} else {
			if ($data->secured) {
				$src = 'com_usernotes.snote';
				$nam = 'snote';
			} else {
				$src = 'com_usernotes.note';
				$nam = 'note';
			}
		}

		// get any data
		$this->_data = $data;
		// Get the form.
		$form = $this->loadForm($src, $nam, ['control' => 'jform', 'load_data' => true]);

		if (empty($form)) {
			return false;
		}

		return $form;
	}


	protected function loadFormData()
	{
		return $this->_data;
	}


	protected function populateState ()
	{
		// Initialize variables
		$app = Factory::getApplication();
		$params = JComponentHelper::getParams('com_usernotes');
		$input = $app->input;

		// album ID
		$nid = $input->get('nid', 0, 'INT');
		$this->state->set('usernote.id', $nid);

		// Load the parameters.
		$this->setState('params', $params);
	}

}
