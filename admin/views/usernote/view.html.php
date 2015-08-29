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
 * View to edit a usernote.
 *
 * @since  1.6
 */
class UserNotesViewUserNote extends JViewLegacy
{
	/**
	 * The item object for the usernote
	 *
	 * @var    JObject
	 * @since  1.6
	 */
	protected $item;

	/**
	 * The form object for the usernote
	 *
	 * @var    JForm
	 * @since  1.6
	 */
	protected $form;

	/**
	 * The model state of the usernote
	 *
	 * @var    JObject
	 * @since  1.6
	 */
	protected $state;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise a Error object.
	 *
	 * @since   1.6
	 */
	public function display($tpl = null)
	{
		$this->state	= $this->get('State');
		$this->item		= $this->get('Item');
		$this->form		= $this->get('Form');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode("\n", $errors));

			return false;
		}

		if ($this->getLayout() == 'modal')
		{
			$this->form->setFieldAttribute('language', 'readonly', 'true');
			$this->form->setFieldAttribute('catid', 'readonly', 'true');
		}

		$this->addToolbar();
		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	protected function addToolbar()
	{
		JFactory::getApplication()->input->set('hidemainmenu', true);

		$user		= JFactory::getUser();
		$isNew		= ($this->item->id == 0);
		$checkedOut	= !($this->item->checked_out == 0 || $this->item->checked_out == $user->get('id'));

		// Since we don't track these assets at the item level, use the category id.
		$canDo		= JHelperContent::getActions('com_usernotes', 'category', $this->item->catid);

		JToolbarHelper::title(JText::_('COM_USERNOTES_MANAGER_USERNOTE'), 'feed usernotes');

		// If not checked out, can save the item.
		if (!$checkedOut && ($canDo->get('core.edit') || count($user->getAuthorisedCategories('com_usernotes', 'core.create')) > 0))
		{
			JToolbarHelper::apply('usernote.apply');
			JToolbarHelper::save('usernote.save');
		}
		if (!$checkedOut && count($user->getAuthorisedCategories('com_usernotes', 'core.create')) > 0)
		{
			JToolbarHelper::save2new('usernote.save2new');
		}
		// If an existing item, can save to a copy.
		if (!$isNew && $canDo->get('core.create'))
		{
			JToolbarHelper::save2copy('usernote.save2copy');
		}

		if (empty($this->item->id))
		{
			JToolbarHelper::cancel('usernote.cancel');
		}
		else
		{
			if ($this->state->params->get('save_history', 0) && $user->authorise('core.edit'))
			{
				JToolbarHelper::versions('com_usernotes.usernote', $this->item->id);
			}

			JToolbarHelper::cancel('usernote.cancel', 'JTOOLBAR_CLOSE');
		}

		JToolbarHelper::divider();
		JToolbarHelper::help('JHELP_COMPONENTS_USERNOTES_FEEDS_EDIT');
	}
}
