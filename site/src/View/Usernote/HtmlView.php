<?php
/**
* @package		com_usernotes
* @copyright	Copyright (C) 2015-2026 RJCreations. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
* @since		1.5.5
*/
namespace RJCreations\Component\Usernotes\Site\View\Usernote;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use RJCreations\Library\RJUserCom;
use RJCreations\Component\Usernotes\Site\View\ViewBase;
use RJCreations\Component\Usernotes\Administrator\Helper\UsernotesHelper;

define('NORMODE', 0);
define('QVUMODE', 1);
define('PRNMODE', 2);
define('PUBMODE', 3);

class HtmlView extends ViewBase
{
	protected $app;
	protected $userid;
	protected $state;
	protected $params;
//	protected $qview;
	protected $dmode = NORMODE;
	protected $smallDevice = false;

	// use alternate css
	protected $usecss = ['unotes','unote'];

	public function display ($tpl = null)
	{
		$this->app = Factory::getApplication();
		$this->userid = Factory::getUser()->id;

		// Get model data.
		$this->state = $this->get('State');
		$this->item = $this->get('Item');

		// flag if printing
		if ($this->state->get('task', 0) === 'printNote') $this->dmode = PRNMODE;
		// flag if e-publish
		if ($this->app->input->get->getInt('nid',0)<0) $this->dmode = PUBMODE;

		// Construct the breadcrumb
		$this->buildPathway($this->item->itemID);

		if ($this->item->secured && !$this->app->input->post->get('ephrase','','string')) {
			$this->form = $this->get('Form');
			$this->_prepareDocument(true);
			return parent::display('ephrase');
		}

		if ($this->item->secured) {
			$cookn = UsernotesHelper::hashCookieName(RJUserCom::getInstObject(), $this->item->itemID, $this->item->contentID);
			$ephrase = $this->app->input->post->get('ephrase','','string');
			$this->item->serial_content = UsernotesHelper::doCrypt($ephrase, $this->item->serial_content, true, $this->item->secured);
			$cookv = UsernotesHelper::doCrypt($this->item->itemID.'-@:'.$this->item->contentID, $ephrase);
			setcookie($cookn, $cookv, 0, '', '', true);
		}

//		$this->qview = $this->app->input->post->get('qview',0,'integer');
		if ($this->app->input->post->get('qview',0,'integer')) $this->dmode = QVUMODE;

		// Check for errors.
		// @TODO: Maybe this could go into ComponentHelper::raiseErrors($this->get('Errors'))
		if (count($errors = $this->get('Errors'))) {
			throw new Exception(implode("\n", $errors), 500);
			return false;
		}

		// Get the component parameters
		$cparams = ComponentHelper::getParams('com_usernotes');
		// Get the current menu item
		$this->params = $this->app->getParams();
		// Meld the params
		if (!$this->params->get('maxUpload')) $this->params->set('maxUpload', $cparams->get('maxUpload', UsernotesHelper::phpMaxUp()));

		// establish the max file upload size
		$this->maxUploadBytes = min($this->params->get('maxUpload'), UsernotesHelper::phpMaxUp());

		$limits = UsernotesHelper::getLimits();
		$this->maxUploadBytes = $limits['maxUpload'];

		// Escape strings for HTML output
		$this->pageclass_sfx = htmlspecialchars($this->params->get('pageclass_sfx', ''));

		$this->_prepareDocument();

		return parent::display($this->dmode==PUBMODE?'epub':$tpl);
	}

}
