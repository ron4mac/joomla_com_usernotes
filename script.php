<?php
/**
 * @package    com_usernotes
 * @copyright  Copyright (C) 2016-2021 RJCreations - All rights reserved.
 * @license    GNU General Public License version 3 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

use Joomla\CMS\Factory;

class com_usernotesInstallerScript
{
	function install ($parent)
	{
		$parent->getParent()->setRedirectURL('index.php?option=com_usernotes');
	}


	function uninstall ($parent)
	{
	}


	function update ($parent)
	{
	}


	function preflight ($type, $parent)
	{
		if (method_exists($parent,'getManifest')) {
			$this->release = $parent->getManifest->version;
		} else {
			$this->release = $parent->get('manifest')->version;
		}
	}

	function postflight ($type, $parent)
	{
		$params['version'] = $this->release;
		$this->setParams($params, true);
		if ($type == 'install') {
			$params['storQuota'] = 268435456;
			$params['maxUpload'] = 4194304;
			$params['hide_secure'] = 1;
			$params['standard_skin'] = '';
			$params['show_version'] = 0;
			$this->setParams($params);
		}
	}


	private function setParams ($param_array, $replace=false)
	{
		if (count($param_array) > 0) {
			// read the existing component value(s)
			$db = Factory::getDbo();
			$db->setQuery('SELECT params FROM #__extensions WHERE name = "com_usernotes"');
			$params = json_decode($db->loadResult(), true);
			// add the new variable(s) to the existing one(s), replacing existing only if requested
			foreach ($param_array as $name => $value) {
				if (!isset($params[(string) $name]) || $replace)
					$params[(string) $name] = (string) $value;
			}
			// store the combined new and existing values back as a JSON string
			$paramsString = json_encode($params);
			$db->setQuery('UPDATE #__extensions SET params = ' . $db->quote($paramsString) . ' WHERE name = "com_usernotes"');
			$db->execute();
		}
	}

}
