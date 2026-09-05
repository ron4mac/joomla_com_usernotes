<?php
/**
* @package		com_usernotes
* @copyright	Copyright (C) 2015-2026 RJCreations. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
* @since		1.6.0
*/
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\Helpers\Sidebar;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use RJCreations\Component\Usernotes\Administrator\Helper\UsernotesHelper;

/**
 * View class for a list of user notes.
 */
class UsernotesView extends BaseHtmlView
{
	protected $items;
	protected $pagination;
	protected $state;

	/**
	 * Display the view
	 */
	public function display ($tpl = null): void
	{
		$this->items = $this->get('Items');
		$this->pagination = $this->get('Pagination');
		$this->state = $this->get('State');	//var_dump($this->state);
		$this->filterForm = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');

		if ((int)JVERSION < 4) $this->addSubmenu($this->relm);

		// Check for errors.
		//		if (count($errors = $this->get('Errors'))) {
		//			JError::raiseError(500, implode("\n", $errors));
		//			return false;
		//		}

		$this->addToolbar();
		$this->sidebar = Sidebar::render();
		parent::display($tpl);
	}


	/**
	 * Add submenu items
	 */
	protected function addSubmenu ($vName)
	{
		Sidebar::addEntry(
			Text::_('COM_USERNOTES_SUBMENU_USER'),
			'index.php?option=com_usernotes',
			$vName == 'user'
		);
		Sidebar::addEntry(
			Text::_('COM_USERNOTES_SUBMENU_GROUP'),
			'index.php?option=com_usernotes&view=groupnotes',
			$vName == 'group'
		);
	}


	protected function addToolbar ()
	{
		$canDo = UsernotesHelper::getActions();

		ToolBarHelper::title(Text::_('COM_USERNOTES_MENU').': '.Text::_('COM_USERNOTES_MANAGER_'.strtoupper($this->relm)), 'stack usernotes');

		ToolBarHelper::deleteList(Text::_('COM_USERNOTES_MANAGER_DELETEOK'));
		//JToolBarHelper::trash('usernotes.trash');

	//	if ($canDo->get('core.edit.state')) {
	//		JToolBarHelper::custom('notes.reset', 'refresh.png', 'refresh_f2.png', 'JUSERSCHED_RESET', false);
	//	}

		ToolBarHelper::custom('convertDb', 'wrench', '', 'Convert database');

		ToolBarHelper::divider();
	//	if ($canDo->get('core.admin')) {
		if ($canDo->{'core.admin'}) {
			ToolBarHelper::preferences('com_usernotes');
		}
		ToolBarHelper::divider();
		ToolBarHelper::help('user_schedulers', true);
	}


	protected function state ($vari, $set=false, $val='', $glb=false)
	{
		$stvar = ($glb?'':'com_usernotes.').$vari;
		$app = Factory::getApplication();
		if ($set) {
			$app->setUserState($stvar, $val);
			return;
		}
		return $app->getUserState($stvar, '');
	}

}
