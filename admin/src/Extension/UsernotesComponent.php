<?php
/**
* @package		com_usernotes
* @copyright	Copyright (C) 2022-2026 RJCreations. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
* @since		1.5.5
*/
namespace RJCreations\Component\Usernotes\Administrator\Extension;

defined('_JEXEC') or die;

use Joomla\CMS\Extension\BootableExtensionInterface;
use Joomla\CMS\Extension\MVCComponent;
use Joomla\CMS\HTML\HTMLRegistryAwareTrait;
use RJCreations\Component\Usernotes\Administrator\Service\Html\MyGrid;
use Psr\Container\ContainerInterface;
	
class UsernotesComponent extends MVCComponent implements BootableExtensionInterface
{
	use HTMLRegistryAwareTrait;

	public function boot(ContainerInterface $container)
	{
		$this->getRegistry()->register('myGrid', new MyGrid());
	}

}
