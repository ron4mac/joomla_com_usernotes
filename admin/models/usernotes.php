<?php
defined('_JEXEC') or die;

jimport('joomla.filesystem.folder');
jimport('joomla.application.component.modellist');

class UsernotesModelUsernotes extends JModelList
{
	protected $relm = 'u';
	protected $_total = -1;

	public function __construct($config = array())
	{   
		$config['filter_fields'] = array('fullname', 'username', 'userid');
		parent::__construct($config);
	}

	public function getItems ()
	{	//return array();
		// Get a storage key.
		$store = $this->getStoreId('list');

		// Try to load the data from internal storage.
		if (isset($this->cache[$store])) {
			return $this->cache[$store];
		}

		jimport('rjuserdata.userdata');
		$unotes = array();
		$folds = UserNotesHelper::getDbPaths($this->relm,'usernotes');
		foreach ($folds as $fold) {
			$userid = (int)substr($fold,1);
			if ($this->relm == 'u') {
				$user = JUser::getInstance($userid);
				$unotes[] = array('name'=>$user->name,'uname'=>$user->username,'uid'=>$userid);
			} else {
				$unotes[] = array('uname'=>UserNotesHelper::getGroupTitle($userid),'name'=>'group','uid'=>$userid);
			}
		}
		$this->_total = count($unotes);

		$start = $this->getState('list.start');
		$limit = $this->getState('list.limit');
		$listOrder = $this->getState('list.ordering');
		$listDirn = $this->getState('list.direction');
	//	echo $listOrder;echo $listDirn;

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
		$this->cache[$store] = array_slice($unotes,$start,$limit?$limit:null);

		return $this->cache[$store];
	}

	public function getTotal ()
	{
		// Get a storage key.
		$store = $this->getStoreId('getTotal');

		// Try to load the data from internal storage.
		if (isset($this->cache[$store])) {
			return $this->cache[$store];
		}

		// Load the total if none
		if ($this->_total < 0) $this->getItems();

		// Add the total to the internal cache.
		$this->cache[$store] = $this->_total;

		return $this->cache[$store];
	}

	protected function populateState ($ordering = null, $direction = null) {
		parent::populateState('username', 'ASC');
	}

}
