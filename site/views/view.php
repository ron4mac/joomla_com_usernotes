<?php
defined('_JEXEC') or die;

define('ITM_CAN_EDIT', 1);
define('ITM_CAN_DELE', 2);
define('ITM_CAN_CREA', 4);

define('IS_SMALL_DEVICE', 0);

use Joomla\Registry\Registry;

JLoader::register('UserNotesHelper', JPATH_COMPONENT_ADMINISTRATOR.'/helpers/usernotes.php');

class UserNotesViewBase extends JViewLegacy
{
	protected $userID;
	protected $notesID;
	protected $access = 0;
	protected $item;
	protected $footMsg;
	protected $attached;

	public function __construct($config = array())
	{
		parent::__construct($config);
		$this->userID = JFactory::getUser()->get('id');
	}

	protected function _prepareDocument()
	{
		if ($this->userID) {
			if (UserNotesHelper::userAuth($this->userID) > 1) {
				if ($this->item && $this->item->checked_out && $this->item->checked_out != $this->userID) {
					$this->footMsg = 'Checked out by '.JFactory::getUser($this->item->checked_out)->get('username').'.';
				} else {
					$this->access = 15;
				}
			}
		}
		// Get a notes instance identifier for ajax/upload
		$this->notesID = UserNotesHelper::getInstanceID();

		$this->attached = @unserialize($this->item->attached);
	}
}