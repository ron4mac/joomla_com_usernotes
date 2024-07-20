<?php
/**
* @package		com_usernotes
* @copyright	Copyright (C) 2015-2024 RJCreations. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
* @since		1.5.0
*/
namespace RJCreations\Component\Usernotes\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\FormController;

\JLoader::register('RJUserCom', JPATH_LIBRARIES . '/rjuser/com.php');
\JLoader::register('UserNotesHelperDb', JPATH_COMPONENT_ADMINISTRATOR.'/helpers/db.php');

class UserNoteController extends FormController
{

	protected function allowAdd ($data = [])
	{
		$user = Factory::getUser();
		$categoryId = JArrayHelper::getValue($data, 'catid', $this->input->getInt('filter_category_id'), 'int');
		$allow = null;

		if ($categoryId) {
			// If the category has been passed in the URL check it.
			$allow = $user->authorise('core.create', $this->option . '.category.' . $categoryId);
		}

		if ($allow === null) {
			// In the absence of better information, revert to the component permissions.
			return parent::allowAdd($data);
		} else {
			return $allow;
		}
	}


	protected function allowEdit ($data = [], $key = 'id')
	{
		$user = Factory::getUser();
		$recordId = (int) isset($data[$key]) ? $data[$key] : 0;
		$categoryId = 0;

		if ($recordId) {
			$categoryId = (int) $this->getModel()->getItem($recordId)->catid;
		}

		if ($categoryId) {
			// The category has been set. Check the category permissions.
			return $user->authorise('core.edit', $this->option . '.category.' . $categoryId);
		} else {
			// Since there is no asset tracking, revert to the component permissions.
			return parent::allowEdit($data, $key);
		}
	}


	public function batch ($model = null)
	{
		JSession::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

		// Set the model
		$model = $this->getModel('UserNote', '', []);

		// Preset the redirect
		$this->setRedirect(JRoute::_('index.php?option=com_usernotes&view=usernotes' . $this->getRedirectToListAppend(), false));

		return parent::batch($model);
	}


	protected function postSaveHook (JModelLegacy $model, $validData = [])
	{

	}

}
