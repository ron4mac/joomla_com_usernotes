<?php
/**
* @package		com_usernotes
* @copyright	Copyright (C) 2015-2024 RJCreations. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
* @since		1.5.0
*/
namespace RJCreations\Component\Usernotes\Site\View\Startup;

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\Helpers\Number;

class HtmlView extends \Joomla\CMS\MVC\View\HtmlView
{
	protected $quota;
	protected $maxfs;

	public function display ($tpl = null)
	{
		$this->auth = (bool)\RJUserCom::getInstObject()->canCreate();

		$limits = \UserNotesHelper::getLimits();
		$this->quota = Number::bytes($limits['storQuota'], 'auto', 0);
		$this->maxfs = Number::bytes($limits['maxUpload'], 'auto', 0);

		return parent::display($tpl);
	}

}