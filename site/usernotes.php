<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_usernotes
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

//require_once JPATH_COMPONENT . '/helpers/route.php';
//JTable::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR . '/tables');

$controller = JControllerLegacy::getInstance('UserNotes');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();
