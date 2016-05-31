<?php
defined('_JEXEC') or die;

require_once JPATH_BASE . '/components/com_usernotes/helpers/usernotes.php';

/**
 * View class for a list of user schedules.
 */
class UsernotesViewUsernotes extends JViewLegacy
{
	protected $items;
	protected $pagination;
	protected $state;

	/**
	 * Display the view
	 */
	public function display($tpl = null)
	{
		$this->items		= $this->get('Items');
		$this->pagination	= $this->get('Pagination');
		$this->state		= $this->get('State');	//var_dump($this->state);

		UserNotesHelper::addSubmenu('user');

		// Check for errors.
		//		if (count($errors = $this->get('Errors'))) {
		//			JError::raiseError(500, implode("\n", $errors));
		//			return false;
		//		}

		$this->addToolbar();
		$this->sidebar = JHtmlSidebar::render();
		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since	1.6
	 */
	protected function addToolbar()
	{
		$canDo	= UserNotesHelper::getActions();

		JToolBarHelper::title(JText::_('COM_USERNOTES_MENU').' : '.JText::_('COM_USERNOTES_MANAGER_UNOTES'), 'stack usernotes');

		JToolBarHelper::deleteList(JText::_('COM_USERNOTES_MANAGER_DELETEOK'));
		//JToolBarHelper::trash('usernotes.trash');

	//	if ($canDo->get('core.edit.state')) {
	//		JToolBarHelper::custom('notes.reset', 'refresh.png', 'refresh_f2.png', 'JUSERSCHED_RESET', false);
	//	}

		JToolBarHelper::divider();
		if ($canDo->get('core.admin')) {
			JToolBarHelper::preferences('com_usernotes');
		}
		JToolBarHelper::divider();
		JToolBarHelper::help('user_schedulers', true);
	}

	protected function state ($vari, $set=false, $val='', $glb=false)
	{
		$stvar = ($glb?'':'com_usernotes.').$vari;
		$app = JFactory::getApplication();
		if ($set) {
			$app->setUserState($stvar, $val);
			return;
		}
		return $app->getUserState($stvar, '');
	}

}
