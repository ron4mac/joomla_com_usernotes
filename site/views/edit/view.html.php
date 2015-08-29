<?php
defined('_JEXEC') or die;

include_once JPATH_COMPONENT.'/views/view.php';

class UserNotesViewEdit extends UserNotesViewBase
{
	protected $type;
	protected $state;
	protected $params;

	protected $smallDevice = false;

	public function display ($tpl = null)
	{
		$app  = JFactory::getApplication();

		// Get view related request variables.
		$this->type = $app->input->get('type','','cmd');

		// Get model data.
		$this->state = $this->get('State');
		$model = $this->getModel();
		$item = $model->getItem($app->input->get('nid',0,'int'));
		if (!$item) {
			$item = (object) array('itemID'=>0,'parentID'=>$app->input->get('pid',0,'int'),'contentID'=>null,'checked_out'=>null);
		} else {
			$model->checkOut($item->itemID);
		}
		$this->item = $item;
		$this->form = $model->getForm($item);

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
