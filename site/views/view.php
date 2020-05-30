<?php
/**
 * @package    com_usernotes
 *
 * @copyright  Copyright (C) 2016-2019 RJCreations - All rights reserved.
 * @license    GNU General Public License version 3 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

define('ITM_CAN_EDIT', 1);
define('ITM_CAN_DELE', 2);
define('ITM_CAN_CREA', 4);
define('IS_SMALL_DEVICE', 0);

JLoader::register('UserNotesHelper', JPATH_COMPONENT_ADMINISTRATOR.'/helpers/usernotes.php');

class UsernotesViewBase extends JViewLegacy
{
	protected $userID;
	protected $notesID;
	protected $access = 0;
	protected $item;
	protected $footMsg;
	protected $attached;

	public function __construct ($config = array())
	{
		parent::__construct($config);
		$this->userID = JFactory::getUser()->get('id');
		if (empty($this->itemId)) {
			$this->itemId = JFactory::getApplication()->input->getInt('Itemid', 0);
		}
	}


	protected function buildPathway ($to)
	{
		$db = $this->getModel()->getDbo();
		$pw = JFactory::getApplication()->getPathway();
		$crums = array();
		while ($to) {
			$db->setQuery('SELECT title,parentID,secured FROM notes WHERE itemID='.$to);
			$r = $db->loadAssoc();
			if ($r['secured']) {
				$r['title'] = base64_decode($r['title']);
			}
			array_unshift($crums, array($r['title'],'index.php?option=com_usernotes&pid='.$to));
			$to = $r['parentID'];
		}
		foreach ($crums as $crum) {
			$pw->addItem($crum[0], $crum[1]);
		}
	}


	protected function _prepareDocument ($ePhrase = false)
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

		if (!$ePhrase) {
			// Get a notes instance identifier for ajax/upload
			$this->notesID = UserNotesHelper::getInstanceID();

			if (isset($this->item->attached)) {
				$this->attached = $this->item->attached;
			}
		}
	}

}
