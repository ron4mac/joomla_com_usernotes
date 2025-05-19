<?php
/**
* @package		com_usernotes
* @copyright	Copyright (C) 2015-2024 RJCreations. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
* @since		1.5.1
*/
namespace RJCreations\Component\Usernotes\Site\Model;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseDriver;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use RJCreations\Library\RJUserCom;

\JLoader::register('UserNotesFileEncrypt', JPATH_COMPONENT.'/classes/file_encrypt.php');

class AtvueModel extends BaseDatabaseModel
{
	protected $_context = 'com_usernotes.usernote';
	protected $_item = null;	// use for cache


	public function __construct ($config = [], $factory = null)
	{
		$db = RJUserCom::getDb();
		$config['dbo'] = $db;
		parent::__construct($config, $factory);
	}


	public function itemIsSecure ($nid)
	{
		if (!$nid) return false;
		$db = $this->getDbo();
		$db->setQuery('SELECT secured FROM notes WHERE itemID='.$nid);
		return $db->loadResult();
	}

	public function atFileProps ($cid, $fnam)
	{
		$db = $this->getDbo();
		$db->setQuery('SELECT * FROM fileatt WHERE contentID='.$cid.' AND attached='.$db->quote($fnam));
		return $db->loadObject();
	}

}