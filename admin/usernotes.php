<?php
/**
 * @package    com_usernotes
 *
 * @copyright  Copyright (C) 2016-2019 RJCreations - All rights reserved.
 * @license    GNU General Public License version 3 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

JHtml::_('behavior.tabstate');

$j_version = preg_replace('#[^0-9\.]#i','',JVERSION);
define('USERNOTES_J30', version_compare($j_version,'3.0.0','>=') ? true : false);

if (!JFactory::getUser()->authorise('core.manage', 'com_usernotes')) {
	return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
}

$controller	= JControllerLegacy::getInstance('UserNotes');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();
