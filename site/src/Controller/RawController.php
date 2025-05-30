<?php
/**
* @package		com_usernotes
* @copyright	Copyright (C) 2015-2025 RJCreations. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
* @since		1.5.2
*/
namespace RJCreations\Component\Usernotes\Site\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;
use Joomla\CMS\MVC\Controller\BaseController;
use RJCreations\Library\RJUserCom;
use RJCreations\Component\Usernotes\Site\Helper\HtmlUsernotes;
use RJCreations\Component\Usernotes\Administrator\Helper\UsernotesHelper;

class RawController extends BaseController
{

	public function addRating ()
	{
		$rate = $this->input->post->getFloat('rate', 0);
		// don't let unauthorized users cause a ratings reset
		if ($rate == 0 && UsernotesHelper::userAuth() < 2) die(json_encode(['err'=>'NOT AUTHORIZED']));
		// add the rating to the item
		$iid = $this->input->post->getInt('iID', 0);
		$m = $this->getModel('usernote');
		echo json_encode($m->addRating($iid, $rate));
	}

	public function addComment ()
	{
		$this->tokenCheck();
		// add the comment to the note
		$nid = $this->input->post->getInt('nid', 0);
		$cmnt = trim($this->input->post->getString('cmntext', ''));
		$who = trim($this->input->post->getString('name', ''));
		$m = $this->getModel('social');
		$newcnt = $m->addComment($nid, $cmnt, Factory::getUser()->id, $who);
		echo json_encode(['htm'=>HtmlUsernotes::cmntActIcon($nid,Text::_('COM_USERNOTES_CMNTNOTE'),1,true)]);
	}

	public function delComment ()
	{
		$this->tokenCheck();
		$cid = $this->input->post->getInt('cmntid', 0);
		$m = $this->getModel('social');
		$cmntcnt = $m->delComment($cid);
		$resp = $cmntcnt ? [] : ['htm'=>HtmlUsernotes::getIcon('cm')];
		echo json_encode($resp);
	}

	public function getComments ()
	{
		$nid = $this->input->post->getInt('nid', 0);
		$m = $this->getModel('social');
		$cmnts = $m->getComments($nid);
		$html = '';
		foreach ($cmnts as $cmnt) {
			$who = ($cmnt['uID'] || empty($cmnt['who'])) ? $this->getUserName($cmnt['uID']) : $cmnt['who'];
			$html .= '<div class="cmnt"><span class="cmnthdr">'.date(Text::_('DATE_FORMAT_LC5'),$cmnt['ctime']).' &nbsp; '.$who.'</span><br>'.$cmnt['comment'];
			if (RJUserCom::getInstObject()->canDelete()) {
				$html .= '<a href="#" class="delcmnt" onclick="UNote.deleteComment(event,'.$cmnt['cmntID'].')">'.HtmlUsernotes::getIcon('xdel').'</a>';
			}
			$html .= '</div>';
		}
		echo json_encode(['htm'=>$html]);
	}

	public function help ()
	{
		$wht = $this->input->post->getCmd('wht', 'general');
		echo Text::_('COM_USERNOTES_HELP_'.strtoupper($wht));
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