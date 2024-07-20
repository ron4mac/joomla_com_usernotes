<?php
/**
* @package		com_usernotes
* @copyright	Copyright (C) 2015-2024 RJCreations. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
* @since		1.5.0
*/
defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Language\Text;

abstract class JHtmlMyGrid
{

	public static function checkall ()
	{
		$html = '<input type="checkbox" name="checkall-toggle" value="" title="'.Text::_('JGLOBAL_CHECK_ALL').'" onclick="Joomla.checkAll(this)" />';
		return $html;
	}

	public static function info ($data)
	{
		if (!is_array($data)) return $data;
		$html = '<dl class="UN-info">';
		foreach ($data as $k=>$v) {
			switch ($k) {
				case 'size':
					$html .= '<dt>'.'Storage Use:'.'</dt><dd>'.JHtmlNumber::bytes($v, 'auto', 1).'</dd>';
					break;
				case 'notes':
					$html .= '<dt>'.'Notes:'.'</dt><dd>'.$v.'</dd>';
					break;
				case 'atts':
					$html .= '<dt>'.'Attachments:'.'</dt><dd>'.$v.'</dd>';
					break;
			}
		}
		return $html.'</dl>';
	}

}