<?php
/**
* @package		com_usernotes
* @copyright	Copyright (C) 2015-2022 RJCreations. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
*/
defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

JLoader::register('UserNotesHelper', JPATH_COMPONENT_ADMINISTRATOR.'/helpers/usernotes.php');
JLoader::register('UserNotesHelperDb', JPATH_COMPONENT_ADMINISTRATOR.'/helpers/db.php');

class UserNotesController extends JControllerLegacy
{

	public function convertDb ()
	{
		$sdp = UserNotesHelper::getStorageBase();
		$cids = $this->input->get('cid',array(),'array');
		$view = $this->input->get('view');
		$tc = $view == 'usernotes' ? '@' : '_';
		foreach ($cids as $cid) {
			list($uid,$iid) = explode('|', $cid);
			$mid = $iid ? ('_'.$iid) : '';
			$msgs = UserNotesHelperDb::convertDb(JPATH_ROOT.'/'.$sdp.'/'.$tc.$uid.'/'.JApplicationHelper::getComponentName().$mid);
			$dbpath = JPATH_ROOT.'/'.$sdp.'/'.$tc.$uid.'/'.JApplicationHelper::getComponentName().$mid;
		}
		$this->setRedirect('index.php?option=com_usernotes&view='.$view, Text::_('COM_USERNOTES_DBUP_DONE').($msgs ? '<br>'.implode('<br>',$msgs) : ''));
	}

}