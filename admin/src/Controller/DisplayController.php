<?php
/**
* @package		com_usernotes
* @copyright	Copyright (C) 2015-2024 RJCreations. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
* @since		1.5.0
*/
namespace RJCreations\Component\Usernotes\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\MVC\Controller\BaseController;

\JLoader::register('RJUserCom', JPATH_LIBRARIES . '/rjuser/com.php');
\JLoader::register('UserNotesHelper', JPATH_COMPONENT_ADMINISTRATOR.'/helpers/usernotes.php');
\JLoader::register('UserNotesHelperDb', JPATH_COMPONENT_ADMINISTRATOR.'/helpers/db.php');

class DisplayController extends BaseController
{
	protected $default_view = 'usernotes';

	public function convertDb ()
	{
		$sdp = \RJUserCom::getStorageBase();
		$cids = $this->input->get('cid',array(),'array');
		$view = $this->input->get('view');
		$tc = $view == 'usernotes' ? '@' : '_';
		foreach ($cids as $cid) {
			list($uid,$iid) = explode('|', $cid);
			$mid = $iid ? ('_'.$iid) : '';
			$msgs = \RJUserCom::updateDb(JPATH_ROOT.'/'.$sdp.'/'.$tc.$uid.'/'.ApplicationHelper::getComponentName().$mid.'/usernotes.db3');
		//	$dbpath = JPATH_ROOT.'/'.$sdp.'/'.$tc.$uid.'/'.JApplicationHelper::getComponentName().$mid;
		}
		$this->setRedirect('index.php?option=com_usernotes&view='.$view, Text::_('COM_USERNOTES_DBUP_DONE').($msgs ? '<br>'.implode('<br>',$msgs) : ''));
	}

}