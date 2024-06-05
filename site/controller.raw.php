<?php
/**
* @package		com_usernotes
* @copyright	Copyright (C) 2015-2024 RJCreations. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
* @since		1.4.1
*/
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;
use Joomla\CMS\MVC\Controller\BaseController;

JLoader::register('HtmlUsernotes', JPATH_COMPONENT . '/helpers/html/usernotes.php');

class UserNotesController extends BaseController
{

	public function addRating ()
	{
		$rate = $this->input->post->getFloat('rate', 0);
		// don't let unauthorized users cause a ratings reset
		if ($rate == 0 && UserNotesHelper::userAuth() < 2) die(json_encode(['err'=>'NOT AUTHORIZED']));
		// add the rating to the item
		$iid = $this->input->post->getInt('iID', 0);
		$m = $this->getModel('usernote');
		echo json_encode($m->addRating($iid, $rate));
	}

	public function addComment ()
	{
		$this->tokenCheck();
	//	// don't let unauthorized users cause a ratings reset
	//	if ($rate == 0 && UserNotesHelper::userAuth() < 2) die(json_encode(['err'=>'NOT AUTHORIZED']));
		// add the comment to the note
		$nid = $this->input->post->getInt('nid', 0);
		$cmnt = $this->input->post->getString('cmntext', '');
		$m = $this->getModel('social');
		$newcnt = $m->addComment($nid, $cmnt, Factory::getUser()->id);
		echo json_encode(['htm'=>HtmlUsernotes::cmntActIcon($nid,Text::_('COM_USERNOTES_CMNTNOTE'),1,true)]);
	}

	public function getComments ()
	{
	//	// don't let unauthorized users cause a ratings reset
	//	if ($rate == 0 && UserNotesHelper::userAuth() < 2) die(json_encode(['err'=>'NOT AUTHORIZED']));
		// add the comment to the note
		$nid = $this->input->post->getInt('nid', 0);
		$m = $this->getModel('social');
		$cmnts = $m->getComments($nid);
		$html = '';
		foreach ($cmnts as $cmnt) {
			$html .= '<div class="cmnt"><span class="cmnthdr">'.date(Text::_('DATE_FORMAT_LC5'),$cmnt['ctime']).' &nbsp; '.$this->getUserName($cmnt['uID']).'</span><br>'.$cmnt['comment'];
			$html .= '</div>';
		}
		echo json_encode(['htm'=>$html]);
	}

/**** private functions ************************/

	private function getUserName ($id)
	{
		static $unams = [0=>'-anonymous-'];
		if (empty($unams[$id])) {
			$unams[$id] = Factory::getUser($id)->username;
		}
		return $unams[$id];
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