<?php
/**
* @package		com_usernotes
* @copyright	Copyright (C) 2015-2022 RJCreations. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
*/
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\HTML\HTMLHelper;

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

	protected $instance;
	protected $jDoc;

	// allow subclass to specify alternate css file
	protected $usecss = 'usernotes';

	public function __construct ($config = [])
	{
		parent::__construct($config);
		$this->userID = Factory::getUser()->get('id');
		if (empty($this->itemId)) {
			$this->itemId = Factory::getApplication()->input->getInt('Itemid', 0);
		}
		$this->instance = Factory::getApplication()->getUserState('com_usernotes.instance', '::');
		$this->jDoc = Factory::getDocument();
		// get static css/js for subclasses
		HTMLHelper::stylesheet('components/com_usernotes/static/css/'.$this->usecss.'.css', ['version' => 'auto']);
		HTMLHelper::_('jquery.framework', false);
		$this->jDoc->addScript('components/com_usernotes/static/js/usernotes.js', ['version' => 'auto']);
	}

	// return an action url for use (mostly) with ajax/javascript
	protected function aUrl ($prms, $sep='&')
	{
		if (is_array($prms)) $prms = http_build_query($prms, '', $sep);
		return Route::_('index.php?option=com_usernotes'.$sep.'Itemid='.$this->itemId.$sep.$prms, false);
	}

	protected function buildPathway ($to)
	{
		$db = $this->getModel()->getDbo();
		$pw = Factory::getApplication()->getPathway();
		$crums = [];
		while ($to) {
			$db->setQuery('SELECT title,parentID,secured FROM notes WHERE itemID='.$to);
			$r = $db->loadAssoc();
			if ($r['secured']) {
				$r['title'] = base64_decode($r['title']);
			}
//			array_unshift($crums, [$r['title'],'index.php?option=com_usernotes&pid='.$to]);
//			array_unshift($crums, [$r['title'], Route::_('index.php?option=com_usernotes&pid='.$to.'&Itemid='.$this->itemId, false)]);
			array_unshift($crums, [$r['title'], $this->aUrl('pid='.$to)]);
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
					$this->footMsg = 'Checked out by '.Factory::getUser($this->item->checked_out)->get('username').'.';
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

	protected function nqMessage ($msg, $svrty)
	{
		Factory::getApplication()->enqueueMessage($msg, $svrty);
	}

}
