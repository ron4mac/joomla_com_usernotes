<?php
/**
 * @package    com_usernotes
 *
 * @copyright  Copyright (C) 2016 RJCreations - All rights reserved.
 * @license    GNU General Public License version 3 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
 
class UserNotesViewImages extends JViewLegacy
{
	public function display ($tpl = null)
	{
		$app  = JFactory::getApplication();
		$user = JFactory::getUser();

		// Get model data.
		$state = $this->get('State');
	//	$item  = $this->get('Item');	//var_dump($item);

		// Check for errors.
		// @TODO: Maybe this could go into JComponentHelper::raiseErrors($this->get('Errors'))
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseWarning(500, implode("\n", $errors));

			return false;
		}

		// Get the current menu item
		$params = $app->getParams();

		// Get the usernote
	//	$usernote = $item;

		// Escape strings for HTML output
	//	$this->pageclass_sfx = htmlspecialchars($params->get('pageclass_sfx'));

		$this->assignRef('params', $params);
	//	$this->assignRef('usernote', $usernote);
		$this->assignRef('state', $state);
	//	$this->assignRef('item', $item);
		$this->assignRef('user', $user);

		return parent::display($tpl);
	}
}