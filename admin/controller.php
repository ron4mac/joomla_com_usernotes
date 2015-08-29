<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_usernotes
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * UserNotes master display controller.
 *
 * @since  1.6
 */
class UserNotesController extends JControllerLegacy
{
	/**
	 * Method to display a view.
	 *
	 * @param   boolean  $cachable   If true, the view output will be cached
	 * @param   array    $urlparams  An array of safe url parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
	 *
	 * @return  JControllerLegacy  This object to support chaining.
	 *
	 * @since   1.5
	 */
	public function display($cachable = false, $urlparams = array())
	{
		require_once JPATH_COMPONENT . '/helpers/usernotes.php';

		$view   = $this->input->get('view', 'usernotes');
		$layout = $this->input->get('layout', 'default');
		$id     = $this->input->getInt('id');

		// Check for edit form.
		if ($view == 'usernote' && $layout == 'edit' && !$this->checkEditId('com_usernotes.edit.usernote', $id))
		{
			// Somehow the person just went to the form - we don't allow that.
			$this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $id));
			$this->setMessage($this->getError(), 'error');
			$this->setRedirect(JRoute::_('index.php?option=com_usernotes&view=usernotes', false));

			return false;
		}

		return parent::display();
	}
}
