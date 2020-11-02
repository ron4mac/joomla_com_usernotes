<?php
/**
 * @package    com_usernotes
 * @copyright  Copyright (C) 2016-2020 RJCreations - All rights reserved.
 * @license    GNU General Public License version 3 or later; see LICENSE.txt
 */
defined('JPATH_PLATFORM') or die;

abstract class JHtmlMyGrid
{

	public static function checkall ()
	{
		if (USERNOTES_J30) {
			$html = JHtml::_('grid.checkall');
		} else {
			$html = '<input type="checkbox" name="checkall-toggle" value="" title="'.JText::_('JGLOBAL_CHECK_ALL').'" onclick="Joomla.checkAll(this)" />';
		}
		return $html;
	}


}