<?php
/**
 * @package    com_usernotes
 *
 * @copyright  Copyright (C) 2016 RJCreations - All rights reserved.
 * @license    GNU General Public License version 3 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

include_once JPATH_COMPONENT.'/views/view.php';

class UserNotesViewSearch extends UserNotesViewBase
{
	protected $state;
	protected $items;
	protected $smallDevice = false;

	public function display ($tpl = null)
	{
		$app = JFactory::getApplication();
		// add to the bread crumb
		$app->getPathWay()->addItem('Search','');

		// Get view related request variables.
		$sterm = $app->input->getString('sterm');

		// Get model data.
		$m = $this->getModel();		//var_dump($sterm);
		$items = $m->search($sterm);	//var_dump($items);

		// Check for errors.
		// @TODO: Maybe this could go into JComponentHelper::raiseErrors($this->get('Errors'))
		if (count($errors = $this->get('Errors'))) {
			JError::raiseWarning(500, implode("\n", $errors));
			return false;
		}

		$this->items = $items;
		$this->_prepareDocument();

		return parent::display($tpl);
	}


	protected function _prepareDocument($ePhrase = false)
	{
		$this->access = 15;
		$this->attached = false;
	}

}