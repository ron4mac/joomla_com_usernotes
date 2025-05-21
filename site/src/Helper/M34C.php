<?php
/**
* @package		com_usernotes
* @copyright	Copyright (C) 2022-2024 RJCreations. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
* @since		1.5.0
*/
namespace RJCreations\Component\Usernotes\Site\Helper;

defined('_JEXEC') or die;

// a helper class to accommodate Joomla 3 and Joomla 4 differences
abstract class M34C
{

	// return the appropriate bootstrap data attribute
	public static function bs ($tag)
	{
		$bs4 = ['dismiss','html','placement','target','toggle'];
		if ((int)JVERSION < 4) return 'data-' . $tag;
		return (in_array($tag, $bs4) ? 'data-bs-' : 'data-') . $tag;
	}

	public static function btn ($which)
	{
		$btns = [
			'p' => ['btn btn-primary','btn btn-primary'],
			's' => ['btn btn-secondary','btn btn-secondary'],
			'ps' => ['btn btn-primary btn-small','btn btn-primary btn-sm'],
			'ss' => ['btn btn-secondary btn-small','btn btn-secondary btn-sm'],
			'pl' => ['btn btn-primary btn-large','btn btn-primary btn-lg'],
			'sl' => ['btn btn-secondary btn-large','btn btn-secondary btn-lg']
		];

		return $btns[$which][(int)JVERSION < 4 ? 0 : 1];
	}

}
