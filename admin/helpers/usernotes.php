<?php
defined('_JEXEC') or die;

abstract class UserNotesHelper
{
	protected static $instanceType = null;
	protected static $ownerID = null;
	protected static $udp = null;

	public static function userDataPath ()
	{
		if (self::$udp) return self::$udp;
		self::getTypeOwner();
		$cmp = JApplicationHelper::getComponentName();
		switch (self::$instanceType) {
			case 0:
				$ndir = '@'. self::$ownerID;
				break;
			case 1:
				$ndir = '_'. self::$ownerID;
				break;
			case 2:
				$ndir = '_0';
				break;
		}

		$dispatcher = JDispatcher::getInstance();
		$results = $dispatcher->trigger('onRjuserDatapath', null);
		$sdp = isset($results[0]) ? trim($results[0]) : '';
		if (!$sdp) $sdp = 'userstor';

		self::$udp = $sdp.'/'.$ndir.'/'.$cmp;
		return self::$udp;
	}

	public static function userAuth ($uid)
	{
		self::getTypeOwner();
		$user = JFactory::getUser();
		$uid = $user->get('id');
		$ugrps = $user->get('groups');	//var_dump('ug:',$ugrps);
		switch (self::$instanceType) {
			case 0:
				return $uid == self::$ownerID ? 2 : 0;
				break;
			case 1:
			case 2:
				return in_array(self::$ownerID, $ugrps) ? 2 : 1;
				break;
		}
	}

	public static function getInstanceID ()
	{
		if (is_null(self::$instanceType)) self::getTypeOwner();
		return base64_encode(self::$instanceType.':'.self::$ownerID);
	}

	// convert string in form n(K|M|G) to an integer value
	public static function to_bytes ($val)
	{
		$val = trim($val);
		$last = strtolower($val[strlen($val)-1]);
		switch($last) {
			case 'g': $val *= 1024;
			case 'm': $val *= 1024;
			case 'k': $val *= 1024;
		}
		return $val;
	}

	// convert integer value to n(K|M|G) string
	public static function to_KMG ($val=0)
	{
		$sizm = 'K';
		if ($val) {
			if (($val % 0x40000000) == 0) {
				$sizm = 'G';
				$val >>= 30;
			} elseif (($val % 0x100000) == 0) {
				$sizm = 'M';
				$val >>= 20;
			} else {
			//	$val >>= 10;
			}
		}
		return $val.$sizm;
	}

	public static function formatBytes ($bytes, $precision=2)
	{
		$units = array('B', 'KB', 'MB', 'GB', 'TB');
		$bytes = max($bytes, 0);
		$pow = floor(($bytes ? log($bytes) : 0) / log(1024));
		$pow = min($pow, count($units) - 1); 
		$bytes /= pow(1024, $pow);
		return round($bytes, $precision) . ' ' . $units[$pow];
	}

	//correctly format a string value from a table before showing it
	public static function fs_db ($value)
	{
		return htmlspecialchars(stripslashes($value));
	}

	private static function getTypeOwner ()
	{
		if (is_null(self::$instanceType)) {
			$app = JFactory::getApplication();
			$notesid = $app->input->getBase64('unID');
			if ($notesid) {
				$nids = explode(':',base64_decode($notesid));
				self::$instanceType = $nids[0];
				self::$ownerID = $nids[1];
			} else {
				$params = $app->getParams();
				self::$instanceType = $params->get('notes_type');
				switch (self::$instanceType) {
					case 0:
						self::$ownerID = JFactory::getUser()->get('id');
						if (!self::$ownerID) self::$ownerID = -1;
						break;
					case 1:
						self::$ownerID = $params->get('group_auth');
						break;
					case 2:
						self::$ownerID = $params->get('site_auth');
						break;
				}
			}
		//var_dump(self::$instanceType,self::$ownerID);
		}
	}

}