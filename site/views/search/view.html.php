<?php
/**
 * @package    com_usernotes
 *
 * @copyright  Copyright (C) 2016-2019 RJCreations - All rights reserved.
 * @license    GNU General Public License version 3 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

include_once JPATH_COMPONENT.'/views/view.php';

class UsernotesViewSearch extends UsernotesViewBase
{
	protected $state;
	protected $items;
	protected $smallDevice = false;

	public function display ($tpl = null)
	{
		$app = JFactory::getApplication();
		// add to the bread crumb
		$app->getPathWay()->addItem(JText::_('COM_USERNOTES_SEARCH'),'');

		// Get view related request variables.
		$sterm = $app->input->getString('sterm');

		// Get model data.
		$m = $this->getModel();
		$items = $m->search($sterm);
		$m->addItemPaths($items);

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