<?php
/**
* @package		com_usernotes
* @copyright	Copyright (C) 2015-2026 RJCreations. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
* @since		1.6.0
*/
namespace RJCreations\Component\Usernotes\Site\Model;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseDriver;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use RJCreations\Library\RJUserCom;

class AtvueModel extends BaseDatabaseModel
{
	protected $_context = 'com_usernotes.usernote';
	protected $_item;	// use for cache


	public function __construct ($config = [], $factory = null)
	{
		$db = RJUserCom::getDb();
		$config['dbo'] = $db;
		parent::__construct($config, $factory);
	}


	public function itemIsSecure ($nid)
	{
		if (!$nid) return false;
		$db = $this->getDatabase();
		$db->setQuery('SELECT secured FROM notes WHERE itemID='.$nid);
		return $db->loadResult();
	}

	public function atFileProps ($cid, $fnam)
	{
		$db = $this->getDatabase();
		$db->setQuery('SELECT * FROM fileatt WHERE contentID='.$cid.' AND attached='.$db->quote($fnam));
		return $db->loadObject();
	}

}