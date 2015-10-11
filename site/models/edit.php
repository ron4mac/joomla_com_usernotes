<?php
defined('_JEXEC') or die;

include_once JPATH_COMPONENT.'/classes/note_class.php';

use Joomla\Registry\Registry;

JLoader::register('UserNotesHelper', JPATH_COMPONENT_ADMINISTRATOR.'/helpers/usernotes.php');

class UserNotesModelEdit extends JModelForm
{
	protected $_context = 'com_usernotes.usernote';
	protected $_data;

	public function __construct ($config = array())
	{
		$udbPath = UserNotesHelper::userDataPath().'/usernotes.db3';
		$db = JDatabaseDriver::getInstance(array('driver'=>'sqlite', 'database'=>$udbPath));

		$config['dbo'] = $db;
		parent::__construct($config);
	}

	public function getItem ($nid=null)
	{
		$nid = (!empty($nid)) ? $nid : (int) $this->getState('usernote.id');
		if (!$nid) return null;
//		$secured = (bool)$this->state->get('parameters.menu')->get('secured') ? : false;
		$data = null;
		try
		{
			$db = $this->getDbo();
			$query = $db->getQuery(true)
				->select('n.*, c.serial_content'/*, a.attached'*/)
//				->from(($secured?'secureds':'notes').' AS n')
				->from('notes AS n')
				->join('LEFT', 'content AS c on c.contentID = n.contentID')
			//	->join('LEFT', 'attach AS a on a.contentID = n.contentID')
				->where('n.itemID = ' . (int) $nid);

			$db->setQuery($query);		//echo'<pre>';var_dump($db->getQuery());echo'</pre>';

			$data = $db->loadObject();		//echo'<xmp>';var_dump($data);echo'</xmp>';

			if (empty($data)) {
				JError::raiseError(404, JText::_('COM_USERNOTES_ERROR_FEED_NOT_FOUND'));
			} else {
				if ($nm = @unserialize($data->serial_content)) {
					$data->serial_content = $nm->rendered();
				}
				$db->setQuery('SELECT attached FROM fileatt WHERE contentID='.$data->contentID);
				$data->attached = $db->loadRowList();
				//echo'<xmp>';var_dump($data);echo'</xmp>';jexit();
			}
		}
		catch (Exception $e)
		{
			$this->setError($e);
		}
		return $data;
	}

	public function checkOut ($nid=null)
	{
		if (!$nid) return true;
		$uid = JFactory::getUser()->get('id');	//var_dump($uid,$nid);
		if (!$uid) return false;
		$db = $this->getDbo();
		$db->setQuery('UPDATE notes SET checked_out = '.$uid.', checked_out_time = '.time().' WHERE itemID == '.$nid);
		$db->execute();
	}

	public function checkIn ($nid=null)
	{
		if (!$nid) return true;
		$uid = JFactory::getUser()->get('id');	//var_dump($uid,$nid);
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

	public function getForm ($data = array(), $loadData = true)
	{
		// Initialize variables
		$app = JFactory::getApplication();
		$input = $app->input;		//echo'<xmp>';var_dump($this->state);echo'</xmp>';
//		$secured = (bool)$this->state->get('parameters.menu')->get('secured') ? : false;

		if ($input->get('type','','cmd') == 'f') {
			$src = 'com_usernotes.fold';
			$nam = 'fold';
		} else {
			if (/*$secured ||*/ $data->secured) {
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
		$form = $this->loadForm($src, $nam, array('control' => 'jform', 'load_data' => true));

		if (empty($form))
		{
			return false;
		}

//		$nid = $this->getState('usernote.id');
//		$params = $this->getState('params');
//		$noteID = $this->_item[$id];
//		$params->merge($contact->params);

		return $form;
	}

	protected function loadFormData()
	{
		return $this->_data;
	}

	protected function populateState ()
	{
		// Initialize variables
		$app = JFactory::getApplication();
		$params = JComponentHelper::getParams('com_usernotes');
		$input = $app->input;

//		// menu params
//		$mparams = $app->getParams();
//		$this->state->set('secured', (bool)$mparams->get('secured', false));

		// album ID
		$nid = $input->get('nid', 0, 'INT');
		$this->state->set('usernote.id', $nid);

		// Load the parameters.
		$this->setState('params', $params);
	}

}
