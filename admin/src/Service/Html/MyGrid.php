<?php
/**
* @package		com_usernotes
* @copyright	Copyright (C) 2022-2026 RJCreations. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
* @since		1.5.5
*/
namespace RJCreations\Component\Usernotes\Administrator\Service\Html;

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\HTML\Helpers\Number;

class MyGrid
{

	public function checkall ()
	{
		$html = HTMLHelper::_('grid.checkall');
		return $html;
	}

	public function info ($data)
	{
		if (!is_array($data)) return $data;
		$html = '<dl class="MDY-info">';
		foreach ($data as $k=>$v) {
			switch ($k) {
				case 'size':
					$html .= '<dt>'.'Storage Use:'.'</dt><dd>'.Number::bytes($v, 'auto', 1).'</dd>';
					break;
				case 'items':
					$html .= '<dt>'.'Items:'.'</dt><dd>'.$v.'</dd>';
					break;
				case 'atts':
					$html .= '<dt>'.'Attachments:'.'</dt><dd>'.$v.'</dd>';
					break;
				case 'warn':
					$html .= '<dt>'.'Warning:'.'</dt><dd>'.$v.'</dd>';
			}
		}
		return $html.'</dl>';
	}

}