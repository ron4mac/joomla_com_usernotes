<?php
/**
* @package		com_usernotes
* @copyright	Copyright (C) 2015-2026 RJCreations. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
* @since		1.6.0
*/
namespace RJCreations\Component\Usernotes\Site\View\Usernote;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use RJCreations\Library\RJUserCom;
use RJCreations\Component\Usernotes\Site\View\ViewBase;
use RJCreations\Component\Usernotes\Site\Model\UsernoteModel;
use RJCreations\Component\Usernotes\Administrator\Helper\UsernotesHelper;

define('NORMODE', 0);
define('QVUMODE', 1);
define('PRNMODE', 2);
define('PUBMODE', 3);

class HtmlView extends ViewBase
{
	public $form;
	public $maxUploadBytes;
	public $pageclass_sfx;

	protected $app;
	protected $state;
	protected $params;
	protected $dmode = NORMODE;

	protected $userCanRate;
	// use alternate css
	protected $usecss = ['unotes','unote'];
	protected $usejs = [];
	protected $epub = '';

	public function __construct ($config = [])
	{
		$this->userCanRate = UsernotesHelper::userCanRate();
		if ($this->userCanRate) $this->usejs[] = 'rater1';
		$this->usejs[] = 'upload5d';
		parent::__construct($config);
	}

	public function display ($tpl = null)
	{
		$m = $this->getModel();

		$this->app = Factory::getApplication();

		// Get model data.
		$this->state = $m->getState();
		$this->item = $m->getItem();

		// flag if printing
		if ($this->state->get('task', 0) === 'printNote') $this->dmode = PRNMODE;
		// flag if e-publish
		if ($this->app->input->get->getInt('nid',0)<0) {
			$this->dmode = PUBMODE;
			$this->epub = $this->app->input->get->getString('d','')=='X'?'epubx':'epub';
		}

		// Construct the breadcrumb
		$this->buildPathway($this->item->itemID);

		if ($this->item->secured && !$this->app->input->post->get('ephrase','','string')) {
			$this->form = $m->getForm();
			$this->_prepareDocument(true);
			return parent::display('ephrase');
		}

		if ($this->item->secured) {
			$cookn = UsernotesHelper::hashCookieName(RJUserCom::getInstObject(), $this->item->itemID, $this->item->contentID);
			$ephrase = $this->app->input->post->get('ephrase','','string');
			$this->item->serial_content = UsernotesHelper::doCrypt($ephrase, $this->item->serial_content, true, $this->item->secured);
			$cookv = UsernotesHelper::doCrypt($this->item->itemID.'-@:'.$this->item->contentID, $ephrase);
			setcookie($cookn, $cookv, ['expires' => 0, 'path' => '', 'domain' => '', 'secure' => true]);
		}

		if ($this->app->input->post->get('qview',0,'integer')) $this->dmode = QVUMODE;

		// Check for errors.
		// @TODO: Maybe this could go into ComponentHelper::raiseErrors($this->get('Errors'))
		if (count($errors = $m->getErrors())) {
			throw new Exception(implode("\n", $errors), 500);
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

		return parent::display($this->epub?:$tpl);
	}

}
