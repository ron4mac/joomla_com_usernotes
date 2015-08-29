<?php
defined('_JEXEC') or die;

include_once JPATH_COMPONENT.'/views/view.php';

class UserNotesViewUserNote extends UserNotesViewBase
{
	protected $state;
	protected $params;
	protected $smallDevice = false;

	public function display ($tpl = null)
	{
		$app  = JFactory::getApplication();

		// Get view related request variables.

		// Get model data.
		$this->state = $this->get('State');
		$this->item = $this->get('Item');	//var_dump($this->item);
		$this->getModel()->buildPathway($this->item->itemID);

		// Check for errors.
		// @TODO: Maybe this could go into JComponentHelper::raiseErrors($this->get('Errors'))
		if (count($errors = $this->get('Errors'))) {
			JError::raiseWarning(500, implode("\n", $errors));
			return false;
		}

		// Get the current menu item
		$this->params = $app->getParams();

		// Escape strings for HTML output
		$this->pageclass_sfx = htmlspecialchars($this->params->get('pageclass_sfx'));

		$this->_prepareDocument();

		return parent::display($tpl);
	}
}
