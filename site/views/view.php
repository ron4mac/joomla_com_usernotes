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
use Joomla\CMS\MVC\View\HtmlView;

define('ITM_CAN_EDIT', 1);
define('ITM_CAN_DELE', 2);
define('ITM_CAN_CREA', 4);
define('IS_SMALL_DEVICE', 0);

JLoader::register('UserNotesHelper', JPATH_COMPONENT_ADMINISTRATOR.'/helpers/usernotes.php');

class UsernotesViewBase extends HtmlView
{
	protected $userID;
	protected $notesID;
	protected $access = 0;
	protected $item;
	protected $footMsg;
	protected $attached;

	protected $instance;
	protected $jDoc;

	// allow subclass to specify alternate css file(s)
	protected $usecss = 'usernotes';
	// allow subclass to specify alternate (or no) js file(s)
	protected $usejs = 'usernotes';

	public function __construct ($config = [])
	{
		parent::__construct($config);
		$this->userID = Factory::getApplication()->getIdentity()->get('id');
		if (empty($this->itemId)) {
			$this->itemId = Factory::getApplication()->input->getInt('Itemid', 0);
		}
		$this->instance = Factory::getApplication()->getUserState('com_usernotes.instance', '::');
		$this->jDoc = Factory::getDocument();
		// get css's for subclasses
		if (!is_array($this->usecss)) $this->usecss = [$this->usecss];
		foreach ($this->usecss as $css) {
			HTMLHelper::stylesheet('components/com_usernotes/static/css/'.$css.'.css', ['version' => 'auto']);
		}
		if ((int)JVERSION<4) HTMLHelper::stylesheet('components/com_usernotes/static/css/legacy.css', ['version' => 'auto']);
		// get js's ... jQuery required for now
//		HTMLHelper::_('jquery.framework', false);
		if (!is_array($this->usejs)) $this->usejs = [$this->usejs];
		foreach ($this->usejs as $js) {
			$this->jDoc->addScript('components/com_usernotes/static/js/'.$js.'.js', ['version' => 'auto']);
		}
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
