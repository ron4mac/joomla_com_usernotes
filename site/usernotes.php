<?php
/**
 * @package    com_usernotes
 * @copyright  Copyright (C) 2016-2021 RJCreations - All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Factory;

define('RJC_DBUG', JDEBUG && file_exists(JPATH_ROOT.'/rjcdev.php'));

$controller = BaseController::getInstance('UserNotes');
$controller->execute(Factory::getApplication()->input->get('task'));
$controller->redirect();
