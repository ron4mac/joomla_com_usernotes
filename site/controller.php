<?php
defined('_JEXEC') or die;

JLoader::register('UserNotesHelper', JPATH_COMPONENT_ADMINISTRATOR.'/helpers/usernotes.php');
JLoader::register('JHtmlUsernotes', JPATH_COMPONENT . '/helpers/html/usernotes.php');

class UserNotesController extends JControllerLegacy
{
	protected $uid;

	public function __construct ($config = array())
	{
		parent::__construct($config);
		if (JDEBUG) { JLog::addLogger(array('text_file'=>'com_usernotes.log.php'), JLog::ALL, array('com_usernotes')); }
		$this->uid = JFactory::getUser()->get('id');
	}

	public function display ($cachable = false, $urlparams = false)
	{
		if ($auth = UserNotesHelper::userAuth($this->uid)) {
			if (($auth > 1) && !file_exists(UserNotesHelper::userDataPath())) {
				$this->input->set('view', 'startup');
			}
			return parent::display($cachable, $urlparams);
		} else {
			$this->setRedirect('index.php');
		}
	}

	public function begin ()
	{
		if (!$this->uid) return;
		$htm = '<!DOCTYPE html><title></title>';
		$udp = UserNotesHelper::userDataPath();
		mkdir($udp.'/attach', 0777, true);
		file_put_contents($udp.'/index.html', $htm);
		file_put_contents($udp.'/attach/index.html', $htm);
		$this->setRedirect(JRoute::_('index.php?option=com_usernotes', false));
	}

	public function search ()
	{
		$view = $this->getView('search', 'html');
		$view->setModel($this->getModel('usernotes'), true);
		$view->display();
	}

	public function printNote ()
	{
		$input = JFactory::getApplication()->input;
		$input->set('tmpl','component');
		$view = $this->getView('usernote', 'html');
		$view->setModel($this->getModel('usernote'), true);
		$view->display();
	}

/** ajax calls **/

	public function attach ()
	{
		$m = $this->getModel('usernotes');
		$notesid = $this->input->get('notesID', '' ,'base64');
		$cid = $this->input->post->get('cID', 0 ,'int');
		$files = $this->input->files->get('attm');
		if (JDEBUG) {
			$fdmp = print_r($files, true);
			JLog::add("attach ... notesID: {$notesid}  note: {$cid}  file(s): {$fdmp}", JLog::INFO, 'com_usernotes');
		}
		$m->add_attached($cid, $files, $notesid);
	}

	public function detach ()
	{
		$m = $this->getModel('usernotes');
		$cid = $this->input->post->get('contentID', 0 ,'int');
		$file = $this->input->post->get('file','','string');
		if (JDEBUG) {
			JLog::add("detach ... note: {$cid}  file: {$file}", JLog::INFO, 'com_usernotes');
		}
		$r = $m->del_attached($cid, $file);
		if ($r) echo $r;
	}

	public function attlist ()
	{
		$m = $this->getModel('usernotes');
		$cid = $this->input->post->get('contentID', 0 ,'int');
		$ined = $this->input->get('inedit', 0 ,'int');
		$atchs = $m->attachments($cid);
		if ($atchs) {
			echo JHtml::_('usernotes.att_list',$atchs,$cid,$ined);
		} else echo '';
	}

	public function cat_hier ()
	{
		$pid = $this->input->post->get('pID', 0 ,'int');
		$m = $this->getModel('usernotes');
		$hier = $m->get_item_hier(JFactory::getUser()->get('id'));
		echo '<span>Move item to:</span><br />';
		echo JHtml::_('usernotes.form_dropdown', 'moveTo', $hier, $pid, 'id="moveTo"');
		echo '<br /><hr />'.JHtml::_('usernotes.form_button', 'moveto','Move','style="float:right" onclick="doMove(true)"');
		echo JHtml::_('usernotes.form_button', 'cancel','Cancel','style="float:right" onclick="doMove(false)"');
	}

	public function movitm ()
	{
		$iid = $this->input->post->get('iID', 0 ,'int');
		$pid = $this->input->post->get('pID', 0 ,'int');
		$m = $this->getModel('usernotes');
		echo $m->moveItem($iid, $pid);
	}

	public function tool ()
	{
		$act = $this->input->post('mnuact');
		$iid = $this->input->post('iID');
		$cid = $this->input->post('cID');
		$this->load->model('content_model','mycmodel');
		$ictnt = $this->mycmodel->get_item($cid, $this->enty_item);
		//call_user_func_array(array($ictnt, $act), array($iid, $cid));
		call_user_func(array($ictnt, $act), $cid);
	}

	public function ajitem ()
	{
		$id = $this->input->post('iID');
		$this->load->model($this->enty_base.'_model','mymodel');
		$item = $this->mymodel->get_item($id);
		$this->load->model($this->enty_item.'_model','myimodel');
		$ctnt = $item->contentID ? $this->myimodel->get_item($item->contentID) : NULL;
		$atch = $item->contentID ? $this->myimodel->get_attached($item->contentID) : NULL;
		$this->load->view($this->enty_base.'/ajax',
			array(
				'contentID'=>$item->contentID,
				$this->enty_item=>$ctnt ? $ctnt->rendered(true) : '[MISSING CONTENT]',
				'attached'=>$atch
				)
			);
	}

/** end ajax calls **/

}
