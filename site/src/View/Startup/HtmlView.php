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
use RJCreations\Library\RJUserCom;
use RJCreations\Component\Usernotes\Administrator\Helper\UsernotesHelper;

class HtmlView extends \Joomla\CMS\MVC\View\HtmlView
{
	protected $quota;
	protected $maxfs;

	public function display ($tpl = null)
	{
		$sitemenu = Factory::getApplication()->getMenu();
		$mnu = $sitemenu->getItem($this->menuid);
		if ($mnu->component !== 'com_usernotes') throw new \Exception('NOT ALLOWED: improper menu item', 400);
//var_dump($this->menuid, RJUserCom::getInstObject(), RJUserCom::getInstObject()->canCreate());
		$this->auth = (bool)($this->menuid && RJUserCom::getInstObject()->canCreate());

		$limits = UsernotesHelper::getLimits();
		$this->quota = Number::bytes($limits['storQuota'], 'auto', 0);
		$this->maxfs = Number::bytes($limits['maxUpload'], 'auto', 0);

		return parent::display($tpl);
	}

}