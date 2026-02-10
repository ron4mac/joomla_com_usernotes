<?php
/**
* @package		com_usernotes
* @copyright	Copyright (C) 2015-2026 RJCreations. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
* @since		1.5.5
*/
defined('_JEXEC') or die;

use Joomla\CMS\Component\Router\RouterFactoryInterface;
use Joomla\CMS\Dispatcher\ComponentDispatcherFactoryInterface;
use Joomla\CMS\Extension\ComponentInterface;
use Joomla\CMS\Extension\MVCComponent;
use Joomla\CMS\Extension\Service\Provider\CategoryFactory;
use Joomla\CMS\Extension\Service\Provider\ComponentDispatcherFactory;
use Joomla\CMS\Extension\Service\Provider\MVCFactory;
use Joomla\CMS\Extension\Service\Provider\RouterFactory;
use Joomla\CMS\HTML\Registry;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use RJCreations\Component\Usernotes\Administrator\Extension\UsernotesComponent;

return new class implements ServiceProviderInterface
{
	public function register(Container $container)
	{
		$container->registerServiceProvider(new MVCFactory('\\RJCreations\\Component\\Usernotes'));
		$container->registerServiceProvider(new ComponentDispatcherFactory('\\RJCreations\\Component\\Usernotes'));
		$container->set(
				ComponentInterface::class,
				function (Container $container)
				{
					$component = new UsernotesComponent($container->get(ComponentDispatcherFactoryInterface::class));
					$component->setMVCFactory($container->get(MVCFactoryInterface::class));
					$component->setRegistry($container->get(Registry::class));
					return $component;
		}
		);
	}
};
