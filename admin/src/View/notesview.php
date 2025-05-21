<?php
/**
* @package		com_usernotes
* @copyright	Copyright (C) 2015-2024 RJCreations. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
* @since		1.5.0
*/
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use RJCreations\Component\Usernotes\Administrator\Helper\UsernotesHelper;

/**
 * View class for a list of user notes.
 */
class UsernotesView extends JViewLegacy
{
	protected $items;
	protected $pagination;
	protected $state;

	/**
	 * Display the view
	 */
	public function display($tpl = null)
	{
		HTMLHelper::stylesheet('administrator/components/com_usernotes/static/usernotes.css', ['version' => 'auto']);

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
		$this->sidebar = JHtmlSidebar::render();
		parent::display($tpl);
	}


	/**
	 * Add submenu items
	 */
	protected function addSubmenu ($vName)
	{
		JHtmlSidebar::addEntry(
			Text::_('COM_USERNOTES_SUBMENU_USER'),
			'index.php?option=com_usernotes',
			$vName == 'user'
		);
		JHtmlSidebar::addEntry(
			Text::_('COM_USERNOTES_SUBMENU_GROUP'),
			'index.php?option=com_usernotes&view=groupnotes',
			$vName == 'group'
		);
//		JHtmlSidebar::addEntry(
//			Text::_('COM_USERNOTES_SUBMENU_SITE'),
//			'index.php?option=com_usernotes&view=site',
//			$vName == 'site'
//		);
	}


	protected function addToolbar ()
	{
		$canDo = UsernotesHelper::getActions();

		JToolBarHelper::title(Text::_('COM_USERNOTES_MENU').': '.Text::_('COM_USERNOTES_MANAGER_'.strtoupper($this->relm)), 'stack usernotes');

		JToolBarHelper::deleteList(Text::_('COM_USERNOTES_MANAGER_DELETEOK'));
		//JToolBarHelper::trash('usernotes.trash');

	//	if ($canDo->get('core.edit.state')) {
	//		JToolBarHelper::custom('notes.reset', 'refresh.png', 'refresh_f2.png', 'JUSERSCHED_RESET', false);
	//	}

		JToolBarHelper::custom('convertDb', 'wrench', '', 'Convert database');

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
		$app = Factory::getApplication();
		if ($set) {
			$app->setUserState($stvar, $val);
			return;
		}
		return $app->getUserState($stvar, '');
	}

}
