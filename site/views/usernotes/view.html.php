<?php
defined('_JEXEC') or die;

include_once JPATH_COMPONENT.'/views/view.php';

class UserNotesViewUserNotes extends UserNotesViewBase
{
	protected $state;
	protected $items;
	protected $parentID = 0;

	public function display ($tpl = null)
	{
	//	$this->userID = JFactory::getUser()->get('id');;

		// Get view related request variables.

		// Get model data.
		$this->state = $this->get('State');		//echo'<xmp>';var_dump($this->state->secured);echo'</xmp>';
		$this->items = $this->get('Items');
		$this->item = $this->getModel()->getItem();		//var_dump($this->item);

		$this->params = JFactory::getApplication()->getParams();

		$this->parentID = $this->state->get('parent.id');
		if (!$this->parentID) {
			$this->posq = $this->get('Posq');
		}
		$this->getModel()->buildPathway($this->parentID);

		// Check for errors.
		// @TODO: Maybe this could go into JComponentHelper::raiseErrors($this->get('Errors'))
		if (count($errors = $this->get('Errors'))) {
			JError::raiseWarning(500, implode("\n", $errors));
			return false;
		}

		$this->_prepareDocument();

		return parent::display($tpl);
	}
}
