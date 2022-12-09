<?php
/**
* @package		com_usernotes
* @copyright	Copyright (C) 2015-2022 RJCreations. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
*/
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

JLoader::register('UserNotesFileEncrypt', JPATH_COMPONENT.'/classes/file_encrypt.php');

class UserNotesModelAtvue extends BaseDatabaseModel
{
	const DBFILE = '/usernotes.db3';
	protected $_context = 'com_usernotes.usernote';
	protected $_storPath = null;
	protected $_item = null;	// use for cache


	public function __construct ($config = [])
	{
		$this->_storPath = UserNotesHelper::userDataPath();
		$udbPath = $this->_storPath.self::DBFILE;
		$db = JDatabaseDriver::getInstance(['driver'=>'sqlite', 'database'=>$udbPath]);

		$config['dbo'] = $db;
		parent::__construct($config);
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
		$db->setQuery('SELECT fsize,mtype FROM fileatt WHERE contentID='.$cid.' AND attached='.$db->quote($fnam));
		return $db->loadObject();
	}

}