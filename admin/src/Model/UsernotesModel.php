<?php
/**
* @package		com_usernotes
* @copyright	Copyright (C) 2015-2024 RJCreations. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
* @since		1.5.0
*/
namespace RJCreations\Component\Usernotes\Administrator\Model;

defined('_JEXEC') or die;

//jimport('joomla.filesystem.folder');
//jimport('joomla.application.component.modellist');
use Joomla\CMS\User\User;
use Joomla\CMS\MVC\Model\ListModel;

class UsernotesModel extends ListModel
{
	protected $relm = 'u';
	protected $_total = -1;

	public function __construct ($config = [], $factory = null)
	{   
		$config['filter_fields'] = ['fullname', 'username', 'userid'];
		parent::__construct($config, $factory);
	}


	public function getItems ()
	{
		// Get a storage key.
		$stork = $this->getStoreId('list');

		// Try to load the data from internal storage.
		if (isset($this->cache[$stork])) {
			return $this->cache[$stork];
		}

		require_once JPATH_COMPONENT.'/helpers/db.php';

		$unotes = [];
//		$folds = UserNotesHelper::getDbPaths($this->relm, 'usernotes', true);
		$folds = \RJUserCom::getDbPaths($this->relm, 'usernotes', true);
		foreach ($folds as $dir => $unis) foreach ($unis as $uni) {
			$msgs = [];
			$ufold = basename(dirname(dirname($uni['path'])));
			$userid = (int)substr($ufold,1);
			$menuid = (int)substr(strrchr($uni['path'], '_'), 1);
			if (!$menuid) $msgs[] = 'Requires alignment with menu item';
			$info = \UserNotesHelperDb::getInfo($uni['path']);
			if (file_exists(JPATH_COMPONENT_ADMINISTRATOR.'/sql/upd_'.$info['dbv'].'.sql')) $msgs[] = 'Database needs to be updated';
			if ($this->relm == 'u') {
				$user = User::getInstance($userid);
				$unotes[] = ['name'=>$user->name,'uname'=>$user->username,'uid'=>$userid.'|'.$menuid, 'info'=>$info, 'msgs'=>$msgs];
			} else {
				$unotes[] = ['uname'=> $userid ? \UserNotesHelper::getGroupTitle($userid) : '[ Site ]','name'=>'group','uid'=>$userid.'|'.$menuid, 'info'=>$info, 'msgs'=>$msgs];
			}
		}
		$this->_total = count($unotes);

		$start = $this->getState('list.start');
		$limit = $this->getState('list.limit');
		$listOrder = $this->getState('list.ordering');
		$listDirn = $this->getState('list.direction');

		foreach ($unotes as $key => $row) {
			$name[$key]  = $row['name'];
			$uname[$key] = $row['uname'];
			$uid[$key] = $row['uid'];
		}
		
		if ($this->_total)
		// Sort the data with volume descending, edition ascending
		// Add $data as the last parameter, to sort by the common key
		switch ($listOrder) {
			case 'username':
				array_multisort($uname, SORT_ASC, $name, SORT_ASC, $uid, SORT_ASC, $unotes);
				break;
			case 'fullname':
				array_multisort($name, SORT_ASC, $uname, SORT_ASC, $uid, SORT_ASC, $unotes);
				break;
			case 'userid':
				array_multisort($uid, SORT_ASC, $uname, SORT_ASC, $name, SORT_ASC, $unotes);
				break;
		}


		// Add the items to the internal cache.
		$this->cache[$stork] = array_slice($unotes,$start,$limit?$limit:null);

		return $this->cache[$stork];
	}


	public function getTotal ()
	{
		// Get a storage key.
		$stork = $this->getStoreId('getTotal');

		// Try to load the data from internal storage.
		if (isset($this->cache[$stork])) {
			return $this->cache[$stork];
		}

		// Load the total if none
		if ($this->_total < 0) $this->getItems();

		// Add the total to the internal cache.
		$this->cache[$stork] = $this->_total;

		return $this->cache[$stork];
	}


	protected function populateState ($ordering = null, $direction = null)
	{
		parent::populateState('username', 'ASC');
	}

}
