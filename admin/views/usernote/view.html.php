<?php
/**
 * @package    com_usernotes
 * @copyright  Copyright (C) 2016-2020 RJCreations - All rights reserved.
 * @license    GNU General Public License version 3 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

use Joomla\CMS\Factory;

class UserNotesViewUserNote extends JViewLegacy
{
	protected $item;
	protected $state;

	public function display ($tpl = null)
	{
		$this->state = $this->get('State');
		$this->item = $this->get('Item');

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		$this->addToolbar();
		parent::display($tpl);
	}


	protected function addToolbar ()
	{
		Factory::getApplication()->input->set('hidemainmenu', true);

		$user = Factory::getUser();
		$isNew = ($this->item->id == 0);
		$checkedOut	= !($this->item->checked_out == 0 || $this->item->checked_out == $user->get('id'));

		// Since we don't track these assets at the item level, use the category id.
		$canDo = JHelperContent::getActions('com_usernotes', 'category', $this->item->catid);

		JToolbarHelper::title(JText::_('COM_USERNOTES_MANAGER_USERNOTE'), 'feed usernotes');

		// If not checked out, can save the item.
		if (!$checkedOut && ($canDo->get('core.edit') || count($user->getAuthorisedCategories('com_usernotes', 'core.create')) > 0)) {
			JToolbarHelper::apply('usernote.apply');
			JToolbarHelper::save('usernote.save');
		}
		if (!$checkedOut && count($user->getAuthorisedCategories('com_usernotes', 'core.create')) > 0) {
			JToolbarHelper::save2new('usernote.save2new');
		}
		// If an existing item, can save to a copy.
		if (!$isNew && $canDo->get('core.create')) {
			JToolbarHelper::save2copy('usernote.save2copy');
		}

		if (empty($this->item->id)) {
			JToolbarHelper::cancel('usernote.cancel');
		} else {
			if ($this->state->params->get('save_history', 0) && $user->authorise('core.edit')) {
				JToolbarHelper::versions('com_usernotes.usernote', $this->item->id);
			}
			JToolbarHelper::cancel('usernote.cancel', 'JTOOLBAR_CLOSE');
		}

		JToolbarHelper::divider();
		JToolbarHelper::help('JHELP_COMPONENTS_USERNOTES_FEEDS_EDIT');
	}

}
