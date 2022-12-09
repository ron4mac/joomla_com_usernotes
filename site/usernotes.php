<?php
/**
* @package		com_usernotes
* @copyright	Copyright (C) 2015-2022 RJCreations. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
*/
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\BaseController;

// register base MVC elements
JLoader::register('UsernotesViewBase', JPATH_COMPONENT.'/views/view.php');

// register the library for common user storage actions
JLoader::register('RJUserCom', JPATH_LIBRARIES . '/rjuser/com.php');

// provide a general helper class for the rest of the component
JLoader::register('UserNotesHelper', JPATH_COMPONENT_ADMINISTRATOR.'/helpers/usernotes.php');

// and a J3/J4 compatability helper
JLoader::register('M34C', JPATH_COMPONENT.'/helpers/m34c.php');

define('RJC_DBUG', JDEBUG && file_exists(JPATH_ROOT.'/rjcdev.php'));

$controller = BaseController::getInstance('UserNotes');
$controller->execute(Factory::getApplication()->input->get('task'));
$controller->redirect();
