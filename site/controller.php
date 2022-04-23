<?php
/**
* @package		com_usernotes
* @copyright	Copyright (C) 2015-2022 RJCreations. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
*/
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\HTML\HTMLHelper;

JLoader::register('UserNotesHelper', JPATH_COMPONENT_ADMINISTRATOR.'/helpers/usernotes.php');
JLoader::register('JHtmlUsernotes', JPATH_COMPONENT . '/helpers/html/usernotes.php');

class UserNotesController extends JControllerLegacy
{
	protected $default_view = 'usernotes';
	protected $uid;
	protected $mnuItm;

	public function __construct ($config = [])
	{
		parent::__construct($config);
		if (JDEBUG) { JLog::addLogger(['text_file'=>'com_usernotes.log.php'], JLog::ALL, ['com_usernotes']); }
		$this->uid = Factory::getUser()->get('id');
		$this->mnuItm = $this->input->getInt('Itemid', 0);
		if ($this->mnuItm) {
			$unid = $this->mnuItm.':'.UserNotesHelper::getStorageDir(true).':'.$this->uid;
			Factory::getApplication()->setUserState('com_usernotes.instance', $unid);
			file_put_contents('APPARMS.TXT',print_r($unid,true),FILE_APPEND);
		}
	}


	public function display ($cachable = false, $urlparams = false)
	{
		if (!file_exists(UserNotesHelper::userDataPath())) {
			//set to a view that has no model
			$this->input->set('view', 'startup');
			$view = $this->getView('startup','html');
		} else {
			$view = $this->getView('usernotes','html');
			// provide the edit model for use, as well
			$view->setModel($this->getModel('edit'));
		}
		$view->itemId = $this->mnuItm;

		return parent::display($cachable, $urlparams);

/*		if ($auth = UserNotesHelper::userAuth($this->uid)) {
			$udp = UserNotesHelper::userDataPath();
			if (($auth > 1) && !file_exists($udp)) {
				$this->input->set('view', 'startup');
				$view = $this->getView('startup','html');
			} else {
				@mkdir($udp, 0755, true);
				$view = $this->getView('usernotes','html');
			}
			$view->itemId = $this->mnuItm;
			return parent::display($cachable, $urlparams);
		} else {
			$this->setRedirect('index.php');
		}*/
	}


	public function begin ()
	{
		if (!$this->uid) return;
		$htm = '<!DOCTYPE html><title></title>';
		$udp = UserNotesHelper::userDataPath();
		mkdir($udp.'/attach', 0777, true);
		file_put_contents($udp.'/index.html', $htm);
		file_put_contents($udp.'/attach/index.html', $htm);
		$this->setRedirect(Route::_('index.php?option=com_usernotes&Itemid='.$this->mnuItm, false));
	}


	public function search ()
	{
		$view = $this->getView('search', 'html');
		$view->setModel($this->getModel('usernotes'), true);
		$view->display();
	}


	public function printNote ()
	{
		$this->input->set('tmpl','component');
		$view = $this->getView('usernote', 'html');
		$view->setModel($this->getModel('usernote'), true);
		$view->display();
	}


	private function attsHtml ($mdl, $cid, $edt)
	{
		$atchs = $mdl->attachments($cid);
		if ($atchs) {
			return HTMLHelper::_('usernotes.att_list', $atchs, $cid, $edt);
		}
		return '';
	}


/** ajax calls **/

	public function attach ()
	{
		$m = $this->getModel('usernote');
		$notesid = $this->input->getBase64('notesID', '');
		$cid = $this->input->post->getInt('cID', 0);
		$files = $this->input->files->get('attm', null, 'raw');
		if (JDEBUG) {
			$fdmp = print_r($files, true);
			JLog::add("attach ... notesID: {$notesid}  note: {$cid}  file(s): {$fdmp}", JLog::INFO, 'com_usernotes');
		}
		$msg = $m->add_attached($cid, $files, $notesid);
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


	public function cat_hier ()
	{
		$pid = $this->input->post->getInt('pID', 0);
		$m = $this->getModel('usernotes');
		$hier = $m->get_item_hier(Factory::getUser()->get('id'));
		echo '<span>Move item to:</span><br />';
		echo HTMLHelper::_('usernotes.form_dropdown', 'moveTo', $hier, $pid, 'id="moveTo"');
		echo '<br /><hr />'.HTMLHelper::_('usernotes.form_button', 'moveto', 'Move', 'style="float:right" onclick="UNote.doMove(true)"');
		echo HTMLHelper::_('usernotes.form_button', 'cancel', 'Cancel', 'style="float:right" onclick="UNote.doMove(false)"');
	}


	public function movitm ()
	{
		$iid = $this->input->post->getInt('iID', 0);
		$pid = $this->input->post->getInt('pID', 0);
		$m = $this->getModel('usernotes');
		echo $m->moveItem($iid, $pid);
	}


	public function addRating ()
	{
		$rate = $this->input->post->getInt('rate', 0);
		if ($rate == 0 && UserNotesHelper::userAuth($this->uid) < 2) die('0:(0):NOT ALLOWED');
		$iid = $this->input->post->getInt('iID', 0);
		$m = $this->getModel('usernote');
		echo $m->addRating($iid, $rate);
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


	public function ajitem ()
	{
		$id = $this->input->post->getInt('iID', 0);
		$this->load->model($this->enty_base.'_model','mymodel');
		$item = $this->mymodel->get_item($id);
		$this->load->model($this->enty_item.'_model','myimodel');
		$ctnt = $item->contentID ? $this->myimodel->get_item($item->contentID) : NULL;
		$atch = $item->contentID ? $this->myimodel->get_attached($item->contentID) : NULL;
		$this->load->view($this->enty_base.'/ajax',
				[
				'contentID'=>$item->contentID,
				$this->enty_item=>$ctnt ? $ctnt->rendered(true) : '[MISSING CONTENT]',
				'attached'=>$atch
				]
			);
	}

/** end ajax calls **/

}
