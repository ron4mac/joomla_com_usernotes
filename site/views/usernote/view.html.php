<?php
/**
* @package		com_usernotes
* @copyright	Copyright (C) 2015-2023 RJCreations. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
* @since		1.3.4
*/
defined('_JEXEC') or die;

use Joomla\CMS\Factory;

class UsernotesViewUsernote extends UsernotesViewBase
{
	protected $app;
	protected $state;
	protected $params;
	protected $qview;
	protected $smallDevice = false;

	// use alternate css
	protected $usecss = ['unotes','unote'];

	public function display ($tpl = null)
	{
		$this->app = Factory::getApplication();

		// Get model data.
		$this->state = $this->get('State');
		$this->item = $this->get('Item');

		// Construct the breadcrumb
		$this->buildPathway($this->item->itemID);

		if ($this->item->secured && !$this->app->input->post->get('ephrase','','string')) {
			$this->form = $this->get('Form');
			$this->_prepareDocument(true);
			return parent::display('ephrase');
		}

		if ($this->item->secured) {
			$cookn = UserNotesHelper::hashCookieName($this->item->itemID, $this->item->contentID);
			$ephrase = $this->app->input->post->get('ephrase','','string');
			$this->item->serial_content = UserNotesHelper::doCrypt($ephrase, $this->item->serial_content, true, $this->item->secured);
			$cookv = UserNotesHelper::doCrypt($this->item->itemID.'-@:'.$this->item->contentID, $ephrase);
			setcookie($cookn, $cookv, 0, '', '', true);
		}

		$this->qview = $this->app->input->post->get('qview',0,'integer');

		// Check for errors.
		// @TODO: Maybe this could go into JComponentHelper::raiseErrors($this->get('Errors'))
		if (count($errors = $this->get('Errors'))) {
			throw new Exception(implode("\n", $errors), 500);
			return false;
		}

		// Get the component parameters
		$cparams = JComponentHelper::getParams('com_usernotes');
		// Get the current menu item
		$this->params = $this->app->getParams();
		// Meld the params
		if (!$this->params->get('maxUpload')) $this->params->set('maxUpload', $cparams->get('maxUpload', UserNotesHelper::phpMaxUp()));

		// establish the max file upload size
		$this->maxUploadBytes = min($this->params->get('maxUpload'), UserNotesHelper::phpMaxUp());

		$limits = UserNotesHelper::getLimits();
		$this->maxUploadBytes = $limits['maxUpload'];

		// Escape strings for HTML output
		$this->pageclass_sfx = htmlspecialchars($this->params->get('pageclass_sfx', ''));

		$this->_prepareDocument();

		return parent::display($tpl);
	}

}
