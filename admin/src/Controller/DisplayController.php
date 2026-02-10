<?php
/**
* @package		com_usernotes
* @copyright	Copyright (C) 2015-2026 RJCreations. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
* @since		1.5.5
*/
namespace RJCreations\Component\Usernotes\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\MVC\Controller\BaseController;
use RJCreations\Library\RJUserCom;

class DisplayController extends BaseController
{
	protected $default_view = 'usernotes';

	public function remove ()
	{
		$this->tokenCheck();
		$cids = $this->input->get('cid',array(),'array');
		$view = $this->input->get('view');
		foreach ($cids as $cid) {
			list($guid,$iid) = explode('|', $cid);
			$mid = $iid ?: '';
			RJUserCom::deleteStorageInstance($guid, $mid);
		}
		$this->setRedirect('index.php?option=com_usernotes&view='.$view, Text::_('COM_USERNOTES_MSG_COMPLETE'));
	}

	public function convertDb ()
	{
		$this->tokenCheck();
		$sdp = RJUserCom::getStorageBase();
		$cids = $this->input->get('cid',array(),'array');
		$view = $this->input->get('view');
		$tc = $view == 'usernotes' ? '@' : '_';
		foreach ($cids as $cid) {
			list($uid,$iid) = explode('|', $cid);
			$mid = $iid ? ('_'.$iid) : '';
			$msgs = RJUserCom::updateDb(JPATH_ROOT.'/'.$sdp.'/'.$tc.$uid.'/'.ApplicationHelper::getComponentName().$mid.'/usernotes.db3');
		//	$dbpath = JPATH_ROOT.'/'.$sdp.'/'.$tc.$uid.'/'.JApplicationHelper::getComponentName().$mid;
		}
		$this->setRedirect('index.php?option=com_usernotes&view='.$view, Text::_('COM_USERNOTES_DBUP_DONE').($msgs ? '<br>'.implode('<br>',$msgs) : ''));
	}

	private function tokenCheck ()
	{
		if (!Session::checkToken()) {
			header('HTTP/1.1 403 Not Allowed');
			jexit(Text::_('JINVALID_TOKEN'));
		}
	}

}