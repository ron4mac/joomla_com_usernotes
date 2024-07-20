<?php
/**
* @package		com_usernotes
* @copyright	Copyright (C) 2015-2024 RJCreations. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
* @since		1.5.0
*/
namespace RJCreations\Component\Usernotes\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\AdminController;

\JLoader::register('RJUserCom', JPATH_LIBRARIES . '/rjuser/com.php');
\JLoader::register('UserNotesHelperDb', JPATH_COMPONENT_ADMINISTRATOR.'/helpers/db.php');

class UserNotesController extends AdminController
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