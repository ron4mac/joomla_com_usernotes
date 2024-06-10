<?php
/**
* @package		com_usernotes
* @copyright	Copyright (C) 2015-2024 RJCreations. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
* @since		1.4.4
*/
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Installer\InstallerScript;
use Joomla\CMS\Log\Log;

class com_usernotesInstallerScript extends InstallerScript
{
	protected $minimumJoomla = '3.8';
	protected $com_name = 'com_usernotes';

	public function install ($parent)
	{
		$parent->getParent()->setRedirectURL('index.php?option='.$this->com_name);
	}


	public function uninstall ($parent)
	{
	}


	public function update ($parent)
	{
	}


	public function preflight ($type, $parent)
	{
		// give the parent first shot
		if (parent::preflight($type, $parent) === false) return false;

		// ensure that SQLite is active in joomla
		$dbs = JDatabaseDriver::getConnectors();
		if (!in_array('sqlite', $dbs) && !in_array('Sqlite', $dbs)) {
			Log::add('Joomla support for SQLite(3) is required for this component.', Log::WARNING, 'jerror');
			return false;
		}

		// ensure that the RJUser library is installed
		include JPATH_LIBRARIES . '/rjuser/com.php';
		if (!class_exists('RJUserCom')) {
			Log::add('The <a href="https://github.com/ron4mac/joomla_lib_rjuser" target="_blank">RJUser Library</a> is required for this component.', Log::WARNING, 'jerror');
			return false;
		}

		// get the version number being installed/updated
		if (method_exists($parent,'getManifest')) {
			$this->release = $parent->getManifest()->version;
		} else {
			$this->release = $parent->get('manifest')->version;
		}
	}

	public function postflight ($type, $parent)
	{
		if ($type == 'uninstall') return;
		$params['version'] = $this->release;
		$this->mySetParams($params, true);
		if ($type == 'install') {
			$params['storQuota'] = 268435456;
			$params['maxUpload'] = 4194304;
			$params['hide_secure'] = true;
			$params['standard_skin'] = '';
			$params['show_version'] = false;
			$this->mySetParams($params);
		}
	}


	private function mySetParams ($param_array=[], $replace=false)
	{
		if (count($param_array) > 0) {
			// read the existing component value(s)
			$db = Factory::getDbo();
			$db->setQuery('SELECT params FROM #__extensions WHERE name = "'.$this->com_name.'"');
			$params = json_decode($db->loadResult(), true);
			// add the new variable(s) to the existing one(s), replacing existing only if requested
			foreach ($param_array as $name => $value) {
				if (!isset($params[(string) $name]) || $replace)
					$params[(string) $name] = (string) $value;
			}
			// store the combined new and existing values back as a JSON string
			$paramsString = json_encode($params);
			$db->setQuery('UPDATE #__extensions SET params = ' . $db->quote($paramsString) . ' WHERE name = "'.$this->com_name.'"');
			$db->execute();
		}
	}

}
