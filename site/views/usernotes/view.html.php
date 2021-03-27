<?php
/**
 * @package    com_usernotes
 * @copyright  Copyright (C) 2016-2021 RJCreations - All rights reserved.
 * @license    GNU General Public License version 3 or later; see LICENSE.txt
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


	public function display ($tpl = null)
	{
		$app = Factory::getApplication();

		// Get view related request variables.

		// Get model data.
		$this->state = $this->get('State');
		$this->items = $this->get('Items');
		$this->item = $this->getModel()->getItem();

		$this->parentID = $this->state->get('parent.id');

		// If at the root, check storage useage and queue a message if near quota
		if (!$this->parentID) {
			$mparams = $app->getParams();	//echo'<xmp>';var_dump($mparams);echo'</xmp>';
			$storQuota = (int) $mparams->get('storQuota', 0);
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

		// Create breadcrumbs to this item
		$this->buildPathway($this->parentID);

		// Check for errors.
		// @TODO: Maybe this could go into JComponentHelper::raiseErrors($this->get('Errors'))
		if (count($errors = $this->get('Errors'))) {
			JError::raiseWarning(500, implode("\n", $errors));
			return false;
		}

		// Get the component parameters
		$this->cparams = JComponentHelper::getParams('com_usernotes');

		$this->_prepareDocument();

		return parent::display($tpl);
	}

}
