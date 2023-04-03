<?php
/**
* @package		com_usernotes
* @copyright	Copyright (C) 2015-2023 RJCreations. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
*/
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

class UsernotesViewSearch extends UsernotesViewBase
{
	protected $state;
	protected $items;
	protected $smallDevice = false;

	// use alternate css
	protected $usecss = 'unotes';

	public function display ($tpl = null)
	{
		$app = Factory::getApplication();
		// add to the bread crumb
		$app->getPathWay()->addItem(Text::_('COM_USERNOTES_SEARCH'),'');

		// Get view related request variables.
		$this->sterm = $app->input->getString('sterm');
	//	$this->sterm = base64_decode(str_replace(['-','_'], ['+','/'], $app->input->get->getString('s',''))) || $this->sterm;

		// Get model data.
		$m = $this->getModel();
		$items = $m->search($this->sterm);
		$m->addItemPaths($items);

		// Check for errors.
		// @TODO: Maybe this could go into JComponentHelper::raiseErrors($this->get('Errors'))
		if (count($errors = $this->get('Errors'))) {
			throw new Exception(implode("\n", $errors), 500);
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