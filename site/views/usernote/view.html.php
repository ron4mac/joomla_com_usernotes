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

//		if ($this->state->secured && !$app->input->post->get('ephrase','','string')) {
		if ($this->item->secured && !$app->input->post->get('ephrase','','string')) {
			return parent::display('ephrase');
		}
	//echo'<xmp>';var_dump($app->input->post->get('ephrase','','string'), $this->item, UserNotesHelper::hashCookieName($this->item->itemID, $this->item->contentID));echo'</xmp>';
//		if ($this->state->secured) {
		if ($this->item->secured) {
			$cookn = UserNotesHelper::hashCookieName($this->item->itemID, $this->item->contentID);
			$ephrase = $app->input->post->get('ephrase','','string');
			$this->item->serial_content = UserNotesHelper::doCrypt($ephrase, base64_decode($this->item->serial_content), true);
			$cookv = UserNotesHelper::doCrypt($this->item->itemID.'-@:'.$this->item->contentID, $ephrase);
			setcookie($cookn, base64_encode($cookv));
		}

		// Check for errors.
		// @TODO: Maybe this could go into JComponentHelper::raiseErrors($this->get('Errors'))
		if (count($errors = $this->get('Errors'))) {
			JError::raiseWarning(500, implode("\n", $errors));
			return false;
		}

		// Get the component parameters
		$cparams = JComponentHelper::getParams('com_usernotes');
		// Get the current menu item
		$this->params = $app->getParams();
		// Meld the params
		if (!$this->params->get('maxUpload')) $this->params->set('maxUpload', $cparams->get('maxUpload'));

		// establish the max file upload size
		$this->maxUploadBytes = min($this->params->get('maxUpload'), UserNotesHelper::phpMaxUp());

		// Escape strings for HTML output
		$this->pageclass_sfx = htmlspecialchars($this->params->get('pageclass_sfx'));

		$this->_prepareDocument();

		return parent::display($tpl);
	}
}
