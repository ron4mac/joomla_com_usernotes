<?php
/**
* @package		com_usernotes
* @copyright	Copyright (C) 2015-2022 RJCreations. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
*/
defined('_JEXEC') or die;

class UserNotesControllerUserNotes extends JControllerAdmin
{

	public function getModel ($name = 'UserNote', $prefix = 'UserNotesModel', $config = ['ignore_request' => true])
	{
		$model = parent::getModel($name, $prefix, $config);
		return $model;
	}


	protected function postDeleteHook (JModelLegacy $model, $ids = null)
	{
	}

}