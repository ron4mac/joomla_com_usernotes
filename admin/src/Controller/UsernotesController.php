<?php
/**
* @package		com_usernotes
* @copyright	Copyright (C) 2015-2026 RJCreations. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
* @since		1.6.0
*/
namespace RJCreations\Component\Usernotes\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\AdminController;

class UserNotesController extends AdminController
{

	public function getModel ($name = 'UserNote', $prefix = 'UserNotesModel', $config = ['ignore_request' => true])
	{
		return parent::getModel($name, $prefix, $config);
	}


	protected function postDeleteHook (JModelLegacy $model, $ids = null)
	{
	}

}