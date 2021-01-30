<?php
/**
 * @package    com_usernotes
 * @copyright  Copyright (C) 2016-2021 RJCreations - All rights reserved.
 * @license    GNU General Public License version 3 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
 
use Joomla\CMS\Factory;

class UsernotesViewStartup extends JViewLegacy
{
	protected $quota;
	protected $maxfs;


	public function display ($tpl = null)
	{
		$limits = UserNotesHelper::getLimits();

		$this->quota = JHtmlNumber::bytes($limits['storQuota'], 'auto', 0);
		$this->maxfs = JHtmlNumber::bytes($limits['maxUpload'], 'auto', 0);

		return parent::display($tpl);
	}

}