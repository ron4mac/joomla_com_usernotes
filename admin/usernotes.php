<?php
/**
 * @package    com_usernotes
 * @copyright  Copyright (C) 2016-2021 RJCreations - All rights reserved.
 * @license    GNU General Public License version 3 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;

JHtml::_('behavior.tabstate');

$j_version = preg_replace('#[^0-9\.]#i','',JVERSION);
define('USERNOTES_J30', version_compare($j_version,'3.0.0','>=') ? true : false);

if (!Factory::getUser()->authorise('core.manage', 'com_usernotes')) {
	return JError::raiseWarning(404, Text::_('JERROR_ALERTNOAUTHOR'));
}

$controller = BaseController::getInstance('UserNotes');
$controller->execute(Factory::getApplication()->input->get('task'));
$controller->redirect();
