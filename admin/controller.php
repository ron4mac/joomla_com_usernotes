<?php
/**
 * @package    com_usernotes
 *
 * @copyright  Copyright (C) 2016 RJCreations - All rights reserved.
 * @license    GNU General Public License version 3 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

JLoader::register('UserNotesHelper', JPATH_COMPONENT_ADMINISTRATOR.'/helpers/usernotes.php');
JLoader::register('UserNotesHelperDb', JPATH_COMPONENT_ADMINISTRATOR.'/helpers/db.php');

class UserNotesController extends JControllerLegacy
{
/*
	public function display ($cachable = false, $urlparams = array())
	{
		require_once JPATH_COMPONENT . '/helpers/usernotes.php';

		$view   = $this->input->get('view', 'usernotes');
		$layout = $this->input->get('layout', 'default');
		$id     = $this->input->getInt('id');

		return parent::display();
	}
*/

	public function convertDb ()
	{
		$sdp = UserNotesHelper::getStorageBase();
		$cids = $this->input->get('cid',array(),'array');
		$view = $this->input->get('view');
		$tc = $view == 'usernotes' ? '@' : '_';
		foreach ($cids as $cid) {
			UserNotesHelperDb::convertDb(JPATH_ROOT.'/'.$sdp.'/'.$tc.$cid.'/'.JApplicationHelper::getComponentName());
		}
		$this->setRedirect('index.php?option=com_usernotes&view='.$view, JText::_('COM_USERSCHED_MSG_COMPLETE'));
	}

}