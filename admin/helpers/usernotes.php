<?php
/**
* @package		com_usernotes
* @copyright	Copyright (C) 2015-2022 RJCreations. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
*/
defined('_JEXEC') or die;

use Joomla\CMS\Factory;

abstract class UserNotesHelper
{
	const COMP = 'com_usernotes';
	protected static $instanceID = null;
	protected static $instanceType = null;
	protected static $instanceObj = null;
	protected static $ownerID = null;
	protected static $udp = null;
	protected static $idp = null;			// instance data path

	public static function getInstanceObject ()
	{
		if (!empty(self::$instanceObj)) return self::$instanceObj;
		$app = Factory::getApplication();
		$menuid = $app->input->getInt('Itemid', 0);
		if (!$menuid) throw new Exception('COM_USERNOTES_MISSING_MENUID');
		$params = $app->getParams();
	//	file_put_contents('APPARMS.TXT',print_r($params,true),FILE_APPEND);
		$user = $app->getIdentity();
		$uid = $user->get('id');
		$ugrps = $user->get('groups');
		$allperms = UnotesInstanceObject::CAN_CREA + UnotesInstanceObject::CAN_EDIT + UnotesInstanceObject::CAN_DELE;
		$path = '';
		$perms = 0;
		switch ($params->get('notes_type')) {
			case 0:	//user
				if ($uid) $perms = $allperms;
				$path = '@'.$uid;
				break;
			case 1:	//group
				$auth = $params->get('group_auth');
				$path = '_'.$auth;
				if ($uid && in_array($auth, $ugrps)) $perms = $allperms;
				break;
			case 2:	//site
				$auth = $params->get('site_auth');
				$path = '_0';
				if ($uid && in_array($auth, $ugrps)) $perms = $allperms;
				break;
		}
		$obj = new UnotesInstanceObject($params->get('notes_type'), $menuid, $uid, $path, $perms);
		file_put_contents('APPARMS.TXT',print_r($obj,true),FILE_APPEND);
		self::$instanceObj = $obj;
		return $obj;
	}

	public static function getInstanceID ()
	{
		if (self::$instanceID) return self::$instanceID;
		$iid = Factory::getApplication()->getUserState('com_usernotes.instance', '');
		$f='';
		if (!$iid) {
			$iid = Factory::getApplication()->getUserStateFromRequest('com_usernotes.instance', 'instance', '');
			$iid2 = Factory::getApplication()->getUserStateFromRequest('com_usernotes.unI', 'unI', '');
			$unI = base64_decode(strtr($iid2, '._-', '+/='));
			$f=' fr '.$unI;
		}
		if ($iid) {
			self::$instanceID = $iid;
		}
		file_put_contents('APPARMS.TXT',print_r(self::$instanceID,true).$f."\n",FILE_APPEND);
		return self::$instanceID;
	}

	public static function getStorageBase ()
	{
		$result = Factory::getApplication()->triggerEvent('onRjuserDatapath', []);
		$sdp = isset($result[0]) ? trim($result[0]) : 'userstor';
		return $sdp;
	}

	public static function getLimits ()
	{
		$app = Factory::getApplication();

		// Get the component parameters
		$cparams = JComponentHelper::getParams(self::COMP);		//var_dump($cparams);
		// Get the instance parameters
		$mparams = $app->getParams();		//var_dump($mparams);

		$storQuota = $cparams->get('storQuota');
		$storQuota = $storQuota?:134217728;
		$maxUpload = $cparams->get('maxUpload');
		$maxUpload = $maxUpload?:4194304;

		$sysMaxUp = JFilesystemHelper::fileUploadMaxSize(false);

		return ['storQuota'=>$storQuota, 'maxUpload'=>min($maxUpload, $sysMaxUp)];
	}

	public static function instanceDataPath ()
	{
		if (self::$idp) return self::$idp;

		$sdp = self::getStorageBase();
		$ndir = self::getStorageDir();
		$cmp = JApplicationHelper::getComponentName().'_'.self::$instanceObj->menuid;

		self::$idp = $sdp.'/'.$ndir.'/'.$cmp;
		return self::$idp;
	}

	public static function userDataPath ()
	{
		if (self::$udp) return self::$udp;

		$sdp = self::getStorageBase();
		$ndir = self::getStorageDir();
		$cmp = JApplicationHelper::getComponentName().'_'.self::$instanceObj->menuid;

		self::$udp = $sdp.'/'.$ndir.'/'.$cmp;
		return self::$udp;
	}

	public static function getStorageDir ($force=false)
	{
		if (!$force) {
			return self::$instanceObj->path;
			list(/*,*/$ddir,) = explode(':', self::getInstanceID()?:':?:');
			return $ddir;
		}

		self::getTypeOwner();
		switch (self::$instanceType) {
			case 0:
				return '@'. self::$ownerID;
				break;
			case 1:
				return '_'. self::$ownerID;
				break;
			case 2:
				return '_0';
				break;
		}
	}

	public static function getDbPaths ($which, $dbname, $full=false, $cmp='')
	{
		$paths = [];
		if (!$cmp) $cmp = JApplicationHelper::getComponentName();
		switch ($which) {
			case 'u':
				$char1 = '@';
				break;
			case 'g':
				$char1 = '_';
				break;
			default:
				$char1 = '';
				break;
		}
		$dpath = JPATH_SITE.'/'.self::getStorageBase().'/';
		if (is_dir($dpath) && ($dh = opendir($dpath))) {
			while (($file = readdir($dh)) !== false) {
				if ($file[0]==$char1) {
					$ptf = $dpath.$file.'/'.$cmp.'/'.$dbname.'.sql3';
					if (file_exists($ptf))
						$paths[] = $full ? $ptf : $file;
					$ptf = $dpath.$file.'/'.$cmp.'/'.$dbname.'.db3';
					if (file_exists($ptf))
						$paths[] = $full ? $ptf : $file;
				}
			}
			closedir($dh);
		}
		return $paths;
	}

	public static function getGroupTitle ($gid)
	{
		$db = Factory::getDbo();
		$db->setQuery('SELECT title FROM #__usergroups WHERE id='.$gid);
		return $db->loadResult();
	}

	public static function hashCookieName ($v1=0, $v2=0)
	{
		$uid = Factory::getApplication()->getIdentity()->get('id');
		return md5(implode(':', [$uid, $v1, $v2]));
	}

	public static function doCrypt ($pass, $dat, $de=false, $sm = 2)
	{
		if ($sm == 2) {	// use OpenSSL
			if ($de) {
				return self::decrypt($dat, $pass);
			} else {
				return self::encrypt($dat, $pass);
			}
		}

		if (!function_exists('mcrypt_module_open')) {
			return '<span sytle="color:red">CAN NOT BE DECODED WITH PHP > 7.1</span>';
		}

		$td = @mcrypt_module_open(MCRYPT_3DES, '', MCRYPT_MODE_ECB, '');
		$iv = @mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_DEV_RANDOM);
		$ks = @mcrypt_enc_get_key_size($td);
		$key = substr($pass, 0, $ks);
		@mcrypt_generic_init($td, $key, $iv);
		if ($de) { $retdat = trim(@mdecrypt_generic($td, base64_decode($dat))); }
		else { $retdat = base64_encode(@mcrypt_generic($td, $dat)); }
		@mcrypt_generic_deinit($td);
		@mcrypt_module_close($td);
		return $retdat;
	}

	// ======================= Alternate encryption method using openssl
	const METHOD = 'aes-256-ctr';

	private static function encrypt ($message, $key)
	{
		$nonceSize = openssl_cipher_iv_length(self::METHOD);
		$nonce = openssl_random_pseudo_bytes($nonceSize);

		$ciphertext = openssl_encrypt(
			$message,
			self::METHOD,
			$key,
			OPENSSL_RAW_DATA,
			$nonce
		);

		return base64_encode($nonce.$ciphertext);
	}

	private static function decrypt ($message, $key)
	{
		$message = base64_decode($message);
		$nonceSize = openssl_cipher_iv_length(self::METHOD);
		$nonce = mb_substr($message, 0, $nonceSize, '8bit');
		$ciphertext = mb_substr($message, $nonceSize, null, '8bit');

		$plaintext = openssl_decrypt(
			$ciphertext,
			self::METHOD,
			$key,
			OPENSSL_RAW_DATA,
			$nonce
		);

		return $plaintext;
	}
	// =======================

	public static function userAuth ($uid)
	{
		self::getTypeOwner();
		$user = Factory::getApplication()->getIdentity();
		$uid = $user->get('id');
		$ugrps = $user->get('groups');
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

	public static function getActions ()
	{
		$user = Factory::getApplication()->getIdentity();
		$result = new JObject;

		$actions = JAccess::getActionsFromFile(JPATH_ADMINISTRATOR . '/components/'.self::COMP.'/access.xml');
		foreach ($actions as $action) {
			$result->set($action->name, $user->authorise($action->name, self::COMP));
		}

		return $result;
	}

	// convert string in form n(K|M|G) to an integer value
	public static function to_bytes ($val)
	{
		$val = trim($val);
		$last = strtolower($val[strlen($val)-1]);
		$val = (int) $val;
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

	public static function formatBytes ($bytes, $precision=2, $sep=' ')
	{
		$units = ['B','KB','MB','GB','TB'];
		$bytes = max($bytes, 0);
		$pow = floor(($bytes ? log($bytes) : 0) / log(1024));
		$pow = min($pow, count($units) - 1); 
		$bytes /= pow(1024, $pow);
		return round($bytes, $precision) . $sep . $units[$pow];
	}

	// return the max file upload size as set by the php config
	public static function phpMaxUp ()
	{
		$u = self::to_bytes(ini_get('upload_max_filesize'));
		$p = self::to_bytes(ini_get('post_max_size'));
		return min($p,$u);
	}

	//correctly format a string value from a table before showing it
	public static function fs_db ($value)
	{
		return htmlspecialchars(stripslashes($value));
	}

	private static function getTypeOwner ()
	{
		if (is_null(self::$instanceType)) {
			$app = Factory::getApplication();
			$notesid = '';	//$app->input->getBase64('unID');
			if ($notesid) {
				$nids = explode(':',base64_decode($notesid));
				self::$instanceType = $nids[0];
				self::$ownerID = $nids[1];
			} else {
				$params = $app->getParams();
				self::$instanceType = $params->get('notes_type');
				switch (self::$instanceType) {
					case 0:
						self::$ownerID = Factory::getApplication()->getIdentity()->get('id') ?: -1;
						break;
					case 1:
						self::$ownerID = $params->get('group_auth');
						break;
					case 2:
						self::$ownerID = $params->get('site_auth');
						break;
				}
			}
		}
	}

}


class UnotesInstanceObject
{
	protected $perms;
	public $type, $menuid, $uid, $path;
	public const CAN_CREA = 1;
	public const CAN_EDIT = 2;
	public const CAN_DELE = 4;

	public function __construct ($type, $menuid, $uid, $path, $perms)
	{
		$this->type = $type;
		$this->menuid = $menuid;
		$this->uid = $uid;
		$this->path = $path;
		$this->perms = $perms;
	}

	public function canCreate ()
	{
		return ($this->perms & self::CAN_CREA);
	}

	public function canEdit ()
	{
		return ($this->perms & self::CAN_EDIT);
	}

	public function canDelete ()
	{
		return ($this->perms & self::CAN_DELE);
	}

}