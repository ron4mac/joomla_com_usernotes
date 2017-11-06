<?php
/**
 * @package    com_usernotes
 *
 * @copyright  Copyright (C) 2016 RJCreations - All rights reserved.
 * @license    GNU General Public License version 3 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
 
class UserNotesViewStartup extends JViewLegacy
{
	protected $quota;
	protected $maxfs;

	public function display ($tpl = null)
	{
		$app = JFactory::getApplication();

		// Get the component parameters
		$cparams = JComponentHelper::getParams('com_usernotes');
		// Get the instance parameters
		$mparams = $app->getParams();

		$this->quota = print_r($cparams,true);
		$this->maxfs = print_r($mparams,true);
		$this->quota = JHtmlNumber::bytes($cparams->get('storQuota'), 'auto', 0);
		$this->maxfs = JHtmlNumber::bytes($cparams->get('maxUpload'), 'auto', 0);
/*
			$storQuota = (int) $mparams->get('storQuota', 0);
			if (!$storQuota) $storQuota = (int) $this->state->cparams->get('storQuota', 0);
			if ($storQuota) {
				$storSize = $this->get('StorSize');
				$posq = $storSize / $storQuota;
				if ($posq > 0.9) {
					$svty = 'notice';
					$msg = JText::sprintf('COM_USERNOTES_NOTICE_QUOTA', UserNotesHelper::formatBytes($storSize, 1, ''), $posq * 100);
					if ($posq > 0.95) {
						$svty = 'warning';
						$msg = '<span style="color:red">'.$msg.'</span>';
					}
					$app->enqueueMessage($msg, $svty);
				}
			}
*/
		return parent::display($tpl);
	}

}