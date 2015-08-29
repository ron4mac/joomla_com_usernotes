<?php
defined('_JEXEC') or die;

//JLoader::register('UserNotesHelper', JPATH_COMPONENT_ADMINISTRATOR.'/helpers/usernotes.php');
JLoader::register('JHtmlUsernotes', JPATH_COMPONENT . '/helpers/html/usernotes.php');

class UserNotesControllerEdit extends JControllerForm
{
	public function __construct ($config = array())
	{
		parent::__construct($config);
		if (JDEBUG) { JLog::addLogger(array('text_file'=>'com_usernotes.log.php'), JLog::ALL, array('com_usernotes')); }
	}

	public function addNote ()
	{
		$this->input->set('view', 'edit');
		$this->display();
	}

	public function editNote ()
	{
		$this->input->set('view', 'edit');
		$this->display();
	}

	public function addFolder ()
	{
		$this->input->set('view', 'edit');
		$this->display();
	}

	public function editFolder ()
	{
		$this->input->set('view', 'edit');
		$this->display();
	}

	public function cancelEdit ()
	{
		$data = $this->input->post->get('jform', array(), 'array');
		// could check-in note (that was checked out)
		$this->getModel()->checkIn($data['itemID']);
		$this->setRedirect(JRoute::_('index.php?option=com_usernotes&pid=' . $data['parentID'], false));
	}

	public function saveNote ()
	{
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$app = JFactory::getApplication();		//echo'<xmp>';var_dump($app->input);jexit();
		$user = JFactory::getUser();
		$model = $this->getModel('usernote');
		$stub = $this->input->getString('id');
		$id = (int) $stub;

		// Get the data from POST
		$data = $this->input->post->get('jform', array(), 'array');

		$model->storeNote($data, $user->get('id'));
		// checkin the item
		$this->getModel()->checkIn($data['itemID']);
		
		$this->setRedirect(JRoute::_('index.php?option=com_usernotes&pid=' . $data['parentID'], false));
	}

	public function saveFolder ()
	{
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$app = JFactory::getApplication();		//echo'<xmp>';var_dump($app->input);jexit();
		$user = JFactory::getUser();
		$model = $this->getModel('usernote');
		$stub = $this->input->getString('id');
		$id = (int) $stub;

		// Get the data from POST
		$data = $this->input->post->get('jform', array(), 'array');
		$data['pid'] = $this->input->post->get('pid', 0, 'int');

		$model->storeFolder($data, $user->get('id'));

		$this->setRedirect(JRoute::_('index.php?option=com_usernotes&id=' . $data['id'], false));
	}

	public function deleteItem ()
	{
		$user = JFactory::getUser();
		if (!$user->get('id')) return;
		$model = $this->getModel('usernote');
		$iid = $this->input->get('iid', 0, 'int');
		$pid = $model->deleteItem($iid);
		$this->setRedirect(JRoute::_('index.php?option=com_usernotes&id=' . $pid, false));
	}
}