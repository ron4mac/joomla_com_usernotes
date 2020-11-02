<?php
/**
 * @package    com_usernotes
 * @copyright  Copyright (C) 2016-2020 RJCreations - All rights reserved.
 * @license    GNU General Public License version 3 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;

JLoader::register('JHtmlUsernotes', JPATH_COMPONENT . '/helpers/html/usernotes.php');

class UsernotesControllerEdit extends JControllerForm
{
	protected $mnuItm;
	protected $uID;

	public function __construct ($config = [])
	{
		parent::__construct($config);
		if (JDEBUG) { JLog::addLogger(['text_file'=>'com_usernotes.log.php'], JLog::ALL, ['com_usernotes']); }
		$this->mnuItm = $this->input->getInt('Itemid', 0);
		$this->uID = Factory::getUser()->get('id');
	}

/*
	public function display ($cachable = false, $urlparams = array())
	{
		$document = $this->app->getDocument();
		$viewType = $document->getType();
		$viewName = $this->input->get('view', $this->default_view);
		$viewLayout = $this->input->get('layout', 'default', 'string');

		$view = $this->getView($viewName, $viewType, '', array('base_path' => $this->basePath, 'layout' => $viewLayout));

		// Get/Create the model
		if ($model = $this->getModel($viewName, '', array('base_path' => $this->basePath)))
		{
			// Push the model into the view (as default)
			$view->setModel($model, true);
		}

		$view->document = $document;
		$view->itemId = $this->mnuItm;

		// Display the view
		$view->display();

		return $this;
	}
*/

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
		$formData = new JInput($this->input->post->get('jform', [], 'array'));
		$iid = $formData->getInt('itemID');
		$pid = $formData->getInt('parentID');
		$isp = $formData->getInt('isParent');

		// could check-in note (that was checked out)
		if ($iid) $this->getModel()->checkIn($iid);

		if ($isp) {
			$whr = 'pid=' . ($iid ?: $pid);
		} elseif ($iid) {
			$whr = 'view=usernote&nid=' . $iid;
		} else {
			$whr = 'pid=' . $pid;
		}
		$this->setRedirect(Route::_('index.php?option=com_usernotes&'.$whr.'&Itemid='.$this->mnuItm, false));
	}


	public function saveNote ()
	{
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$model = $this->getModel('usernote');

		// Get the data from POST
		$formData = new JInput($this->input->post->get('jform', [], 'array'));

		$model->storeNote($formData, $this->uID);
		// checkin the item
		$this->getModel()->checkIn($formData->getInt('itemID'));
		
		$this->setRedirect(Route::_('index.php?option=com_usernotes&pid='.$formData->getInt('parentID').'&Itemid='.$this->mnuItm, false));
	}


	public function saveFolder ()
	{
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$model = $this->getModel('usernote');

		// Get the data from POST
		$formData = new JInput($this->input->post->get('jform', [], 'array'));

		$pid = $model->storeFolder($formData, $this->uID);

		$this->setRedirect(Route::_('index.php?option=com_usernotes&pid='.$pid.'&Itemid='.$this->mnuItm, false));
	}


	public function deleteItem ()
	{
		if (!$this->uID) return;
		$model = $this->getModel('usernote');
		$iid = $this->input->get('iid', 0, 'int');
		$pid = $model->deleteItem($iid);
		$this->setRedirect(Route::_('index.php?option=com_usernotes&pid='.$pid.'&Itemid='.$this->mnuItm, false));
	}

}