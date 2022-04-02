<?php
/**
* @package		com_usernotes
* @copyright	Copyright (C) 2015-2022 RJCreations. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
*/
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\BaseController;

define('RJC_DBUG', JDEBUG && file_exists(JPATH_ROOT.'/rjcdev.php'));

$controller = BaseController::getInstance('UserNotes');
$controller->execute(Factory::getApplication()->input->get('task'));
$controller->redirect();
