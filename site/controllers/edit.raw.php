<?php
/**
* @package		com_usernotes
* @copyright	Copyright (C) 2015-2022 RJCreations. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
*/
defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;
use Joomla\CMS\MVC\Controller\BaseController;

JLoader::register('UserNotesHelper', JPATH_COMPONENT_ADMINISTRATOR.'/helpers/usernotes.php');
JLoader::register('JHtmlUsernotes', JPATH_COMPONENT . '/helpers/html/usernotes.php');

class UsernotesControllerEdit extends BaseController
{
	protected $instanceObj;
	protected $app;

	public function __construct ($config = [], MVCFactoryInterface $factory = null, $app = null, $input = null)
	{	//file_put_contents('REQUEST.txt',print_r($input,true),FILE_APPEND);
		parent::__construct($config, $factory, $app, $input);
		if (JDEBUG) { JLog::addLogger(['text_file'=>'com_usernotes.log.php'], JLog::ALL, ['com_usernotes']); }
		$this->app = $app;
		$this->instanceObj = UserNotesHelper::getInstanceObject();
	}


/**** ajax calls *******************************/

	public function saveFolder ()
	{
		// Check for request forgeries.
		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

		$model = $this->getModel('usernote');

		// Get the data from POST
		$formData = new JInput($this->input->post->get('jform', [], 'array'));
		file_put_contents('APPARMS.TXT',print_r($formData,true),FILE_APPEND);

		// Check permissions
		if (!(($formData->getInt('itemID', 0) && $this->instanceObj->canEdit()) || $this->instanceObj->canCreate())) jexit(Text::_('JERROR_ALERTNOAUTHOR'));

		$pid = $model->storeFolder($formData, $this->instanceObj->uid);
	}


	public function cat_hier ()
	{
		$pid = $this->input->post->getInt('pID', 0);
		$m = $this->getModel('usernotes');
		$hier = $m->get_item_hier($this->instanceObj->uid);
		echo '<span>Move item to:</span><br />';
		echo JHtmlUsernotes::form_dropdown('moveTo', $hier, $pid, 'id="moveTo"');
		echo '<br /><hr />'.JHtmlUsernotes::form_button('moveto', 'Move', 'style="float:right" onclick="UNote.doMove(true)"');
		echo JHtmlUsernotes::form_button('cancel', 'Cancel', 'style="float:right" onclick="UNote.doMove(false)"');
	}


	public function movitm ()
	{
		$iid = $this->input->post->getInt('iID', 0);
		$pid = $this->input->post->getInt('pID', 0);
		$m = $this->getModel('usernotes');
		echo $m->moveItem($iid, $pid);
	}


	public function tool ()
	{
		$act = $this->input->post->getCmd('mnuact','');
		$iid = $this->input->post->getInt('iID', 0);
		$cid = $this->input->post->getInt('cID', 0);
	//	$this->load->model('content_model', 'mycmodel');
	//	$ictnt = $this->mycmodel->get_item($cid, $this->enty_item);
		$m = $this->getModel('usernote');
		call_user_func([$m, $act], $cid);
	}


	public function attach ()
	{
		$this->tokenCheck();

		$m = $this->getModel('usernote');
		$iid = $this->input->post->getInt('iID', 0);
		$cid = $this->input->post->getInt('cID', 0);
		$files = $this->input->files->get('attm', null, 'raw');
		if (JDEBUG) {
			$fdmp = print_r($files, true);
			JLog::add("attach ... notesID: {$notesid}  note: {$cid}  file(s): {$fdmp}", JLog::INFO, 'com_usernotes');
		}
		$key = false;
		if ($m->itemIsSecure($iid)) {
			$cookn = UserNotesHelper::hashCookieName($iid, $cid);
			$cookv = $this->input->cookie->getBase64($cookn);
			$key = UserNotesHelper::doCrypt($iid.'-@:'.$cid, $cookv, true);
		}
		$msg = $m->add_attached($cid, $files, $key);
		if ($msg) { header($_SERVER['SERVER_PROTOCOL'].' 500 '.$msg); jexit(); }
	}


	public function detach ()
	{
		$m = $this->getModel('usernote');
		$cid = $this->input->post->getInt('contentID', 0);
		$file = $this->input->post->getString('file', '');
		if (JDEBUG) {
			JLog::add("detach ... note: {$cid}  file: {$file}", JLog::INFO, 'com_usernotes');
		}
		$resp = [];
		$r = $m->deleteAttachment($cid, $file);
		if ($r) {
			$resp['err'] = $r;
		} else {
			$resp['htm'] = $this->attsHtml($m, $cid, true);
		}
		echo json_encode($resp);
	}


	public function renAttach ()
	{
		$m = $this->getModel('usernote');
		$cid = $this->input->post->getInt('contentID', 0);
		$file = $this->input->post->getString('file', '');
		$tofile = $this->input->post->getString('tofile', '');
		if (JDEBUG) {
			JLog::add("renAttach ... note: {$cid}  file: {$file} tofile: {$tofile}", JLog::INFO, 'com_usernotes');
		}
		$resp = [];
		$r = $m->renameAttachment($cid, $file, $tofile);
		if ($r) {
			$resp['err'] = $r;
		} else {
			$resp['htm'] = $this->attsHtml($m, $cid, true);
		}
		echo json_encode($resp);
	}


	public function attlist ()
	{
		$m = $this->getModel('usernote');
		$cid = $this->input->post->getInt('contentID', 0);
		$ined = $this->input->getInt('inedit', 0);
		echo $this->attsHtml($m, $cid, $ined);
	}


/**** private functions ************************/

	private function attsHtml ($mdl, $cid, $edt)
	{
		$atchs = $mdl->attachments($cid);
		if ($atchs) {
			return JHtmlUsernotes::att_list($atchs, $cid, $edt);
		}
		return '';
	}

	private function tokenCheck ()
	{
		if (!Session::checkToken()) {
			//$this->app->setHeader('status', 401, true);
			header('HTTP/1.1 401 Unauthorized');
			jexit(Text::_('JINVALID_TOKEN'));
		}
	}

}