<?php
/**
* @package		com_usernotes
* @copyright	Copyright (C) 2015-2023 RJCreations. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
* @since		1.3.4
*/
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;
use Joomla\CMS\MVC\Controller\FormController;

JLoader::register('HtmlUsernotes', JPATH_COMPONENT . '/helpers/html/usernotes.php');

class UsernotesControllerEdit extends FormController
{
	protected $instanceObj;

	public function __construct ($config = [], MVCFactoryInterface $factory = null, $app = null, $input = null)
	{	//file_put_contents('REQUEST.txt',print_r($input,true),FILE_APPEND);
		parent::__construct($config, $factory, $app, $input);
		if (JDEBUG) { JLog::addLogger(['text_file'=>'com_usernotes.log.php'], JLog::ALL, ['com_usernotes']); }
		$this->instanceObj = UserNotesHelper::getInstanceObject();

		// fail if public access attempt to a 'user' instance
		if ($this->instanceObj->type == 0 && !$this->instanceObj->uid) throw new Exception(Text::_('JERROR_ALERTNOAUTHOR'), 403);

		HtmlUsernotes::setInstance($this->instanceObj);
	}


	public function display ($cachable = false, $urlparams = false)
	{
		if ($iv = $this->input->get('view', null)) {
			$iview = $this->getView($iv,'html');
//			$iview->unI = strtr(base64_encode($this->unI), '+/=', '._-');
		}
		return parent::display($cachable, $urlparams);
	}


	public function addNote ()
	{
		$this->input->set('view', 'edit');
		$this->display();
	}


	public function editNote ()
	{
		$nid = $this->input->getInt('nid', 0);
		list($ckd,$unm) = $this->getModel()->checkedOut($nid);
		if ($ckd && $ckd != $this->instanceObj->uid) {
			HtmlUsernotes::nqMessage(Text::sprintf('COM_USERNOTES_CHECKED_OUT',$unm), 'warning');
			$this->setRedirect(Route::_('index.php?option=com_usernotes&view=usernote&nid='.$nid.'&Itemid='.$this->instanceObj->menuid, false));
		} else {
			$this->input->set('view', 'edit');
			$this->display();
		}
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
		$this->setRedirect(Route::_('index.php?option=com_usernotes&'.$whr.'&Itemid='.$this->instanceObj->menuid, false));
	}


	public function saveNote ()
	{
		// Check for request forgeries.
		if (!Session::checkToken()) throw new Exception(Text::_('JINVALID_TOKEN'), 401);

		// Get the data from POST
		$formData = new JInput($this->input->post->get('jform', [], 'array'));

		// Check permissions
		if (!(($formData->getInt('itemID', 0) && $this->instanceObj->canEdit()) || $this->instanceObj->canCreate())) throw new Exception(Text::_('JERROR_ALERTNOAUTHOR'), 403);

		$model = $this->getModel('usernote');
		try {
			$model->storeNote($formData, $this->instanceObj->uid);
			if ($errs = $model->getErrors()) {
				$erm = [];
				foreach ($errs as $err) {
					if (is_object($err)) {
						$erm[] = $err->getMessage();
					} else {
						$erm[] = $err;
					}
				}
				Factory::getApplication()->enqueueMessage(implode('<br>', $erm), 'error');
			}
		}
		catch (Exception $e) {
			Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');
		}

		// checkin the item
		$this->getModel()->checkIn($formData->getInt('itemID'));

		$this->setRedirect(Route::_('index.php?option=com_usernotes&pid='.$formData->getInt('parentID').'&Itemid='.$this->instanceObj->menuid, false));
	}


	public function deleteItem ()
	{
		// Check for request forgeries.
		if (!Session::checkToken()) throw new Exception(Text::_('JINVALID_TOKEN'), 401);

		if (!$this->instanceObj->canDelete()) throw new Exception(Text::_('JERROR_ALERTNOAUTHOR'), 403);
//		if (!$this->instanceObj->uid) return;
		$model = $this->getModel('usernote');
		$iid = $this->input->get('iid', 0, 'int');
		$pid = $model->deleteItem($iid);
		$this->setRedirect(Route::_('index.php?option=com_usernotes&pid='.$pid.'&Itemid='.$this->instanceObj->menuid, false));
	}


}