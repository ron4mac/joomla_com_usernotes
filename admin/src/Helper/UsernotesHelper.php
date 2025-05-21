<?php
/**
* @package		com_usernotes
* @copyright	Copyright (C) 2015-2025 RJCreations. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
* @since		1.5.1
*/
namespace RJCreations\Component\Usernotes\Administrator\Helper;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Filesystem\FilesystemHelper;
use RJCreations\Library\RJUserCom;

abstract class UsernotesHelper
{
	const COMP = 'com_usernotes';
	protected static $instanceType = null;
	protected static $instanceObj = null;
	protected static $ownerID = null;

	public static function getLimits ()	// SO
	{
		$app = Factory::getApplication();

		// Get the component parameters
		$cparams = ComponentHelper::getParams(self::COMP);		//var_dump($cparams);
		// Get the instance parameters
		$mparams = $app->getParams();		//var_dump($mparams);

		$storQuota = $cparams->get('storQuota');
		$storQuota = $storQuota?:134217728;
		$maxUpload = $cparams->get('maxUpload');
		$maxUpload = $maxUpload?:4194304;

		$sysMaxUp = FilesystemHelper::fileUploadMaxSize(false);

		return ['storQuota'=>$storQuota, 'maxUpload'=>min($maxUpload, $sysMaxUp)];
	}

	public static function getGroupTitle ($gid)	// AO
	{
		$db = Factory::getDbo();
		$db->setQuery('SELECT title FROM #__usergroups WHERE id='.$gid);
		return $db->loadResult();
	}

	public static function hashCookieName ($instObj, $v1=0, $v2=0)	// SO
	{
		//$uid = self::$instanceObj->uid;
		$uid = $instObj->uid;
		return md5(implode(':', [$uid, $v1, $v2]));
	}

	public static function doCrypt ($pass, $dat, $de=false, $sm = 2)	// SO
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

	public static function userCanRate ()	// SO
	{
	//	self::getTypeOwner();
		$user = (int)JVERSION > 3 ? Factory::getApplication()->getIdentity() : Factory::getUser();
		$uid = $user->get('id');
		return ($uid || Factory::getApplication()->getParams()->get('pubrate', false));
	}

	public static function userAuth ()	// SO vet this more
	{
		self::getTypeOwner();
		$user = (int)JVERSION > 3 ? Factory::getApplication()->getIdentity() : Factory::getUser();
		$uid = $user->get('id');
		$ugrps = $user->get('groups');
		switch (self::$instanceType) {
			case 0:
				return $uid == self::$ownerID ? 2 : 0;
				break;
			case 1:
			case 2:
				return array_intersect((array)self::$ownerID, $ugrps) ? 2 : 1;
				break;
		}
	}

	private static function getTypeOwner ()	// SO vet this more
	{
		if (is_null(self::$instanceType)) {
			$app = Factory::getApplication();
			$notesid = '';	//$app->input->getBase64('unID');
			if ($notesid) {
				$nids = explode(':',base64_decode($notesid));
				self::$instanceType = $nids[0];
				self::$ownerID = $nids[1];
			} else {
				$params = $app->getParams();	echo'<xmp>';var_dump($params,debug_backtrace(2,5));echo'</xmp>';
				self::$instanceType = $params->get('notes_type');
				switch (self::$instanceType) {
					case 0:
						self::$ownerID = self::$instanceObj->uid ?: -1;
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

	public static function getActions ()	// AO
	{
		$user = (int)JVERSION > 3 ? Factory::getApplication()->getIdentity() : Factory::getUser();
		$result = new \JObject;

		$actions = \JAccess::getActionsFromFile(JPATH_ADMINISTRATOR . '/components/'.self::COMP.'/access.xml');
		foreach ($actions as $action) {
			$result->set($action->name, $user->authorise($action->name, self::COMP));
		}

		return $result;
	}

	// convert string in form n(K|M|G) to an integer value
	private static function to_bytes ($val)	// SO
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

	public static function formatBytes ($bytes, $precision=2, $sep=' ')	// SO
	{
		$units = ['B','KB','MB','GB','TB'];
		$bytes = max($bytes, 0);
		$pow = floor(($bytes ? log($bytes) : 0) / log(1024));
		$pow = min($pow, count($units) - 1); 
		$bytes /= pow(1024, $pow);
		return round($bytes, $precision) . $sep . $units[$pow];
	}

	// return the max file upload size as set by the php config
	public static function phpMaxUp ()	// SO
	{
		$u = self::to_bytes(ini_get('upload_max_filesize'));
		$p = self::to_bytes(ini_get('post_max_size'));
		return min($p,$u);
	}

	//correctly format a string value from a table before showing it
	public static function fs_db ($value)	// SO
	{
		return htmlspecialchars(stripslashes($value));
	}

}
