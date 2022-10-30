<?php
/**
* @package		com_usernotes
* @copyright	Copyright (C) 2015-2022 RJCreations. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
*/
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

include_once JPATH_COMPONENT.'/views/view.php';

class UsernotesViewUsernotes extends UsernotesViewBase
{
	protected $state;
	protected $items;
	protected $parentID = 0;

	// use alternate css
	protected $usecss = 'unotes';

	public function display ($tpl = null)
	{
		$app = Factory::getApplication();

		// Get view related request variables.

		// Get model data.
		$this->state = $this->get('State');
		$this->items = $this->get('Items');
		$this->item = $this->getModel()->getItem();
		if (!$this->item) $this->item = (object) ['parentID'=>0, 'secured'=>false, 'checked_out'=>false];

		$this->parentID = $this->state->get('parent.id');

		// Create breadcrumbs to this item
		$this->buildPathway($this->parentID);

		// Check for errors.
		// @TODO: Maybe this could go into JComponentHelper::raiseErrors($this->get('Errors'))
		if (count($errors = $this->get('Errors'))) {
			throw new Exception(implode("\n", $errors), 500);
			return false;
		}

		// Get the component parameters
		$this->cparams = JComponentHelper::getParams('com_usernotes');
		// and the menu instance parameters
		$this->mparams = $app->getParams();	//echo'<xmp>';var_dump($this->mparams);echo'</xmp>';
		

		$this->_prepareDocument();

		// If user can edit and at the root, check storage useage and queue a message if near quota
		if ($this->access && !$this->parentID) {
			$storQuota = (int) $this->mparams->get('storQuota', 0);
			if (!$storQuota) $storQuota = (int) $this->state->cparams->get('storQuota', 0);
			if ($storQuota) {
				$storSize = $this->get('StorSize');
				$posq = $storSize / $storQuota;
				if ($posq > 0.8) {
					$svty = 'notice';
					$msg = Text::sprintf('COM_USERNOTES_NOTICE_QUOTA', UserNotesHelper::formatBytes($storSize, 1, ''), $posq * 100);
					if ($posq > 0.9) {
						$svty = 'warning';
						$msg = '<span style="color:red">'.$msg.'</span>';
					}
					$app->enqueueMessage($msg, $svty);
				}
			}
		}

		return parent::display($tpl);
	}

}
