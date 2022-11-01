<?php
/**
* @package		com_usernotes
* @copyright	Copyright (C) 2015-2022 RJCreations. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
*/
defined('_JEXEC') or die('Restricted access');
 
use Joomla\CMS\Factory;

JLoader::register('UserNotesHelper', JPATH_COMPONENT_ADMINISTRATOR.'/helpers/usernotes.php');
JLoader::register('UserNotesFileEncrypt', JPATH_COMPONENT.'/classes/file_encrypt.php');

class UsernotesViewAtvue extends JViewLegacy
{
	protected $fnam;
	protected $fpath;
	protected $mime;
	protected $down = false;


	public function display ($tpl = null)
	{
		$app = Factory::getApplication();
		$this->jDoc = Factory::getDocument();

		// Get view related request variables.
		$this->down = $app->input->get('down',0,'int');
		$cat = explode('|',$app->input->getString('cat'),3);
		$this->fnam = $cat[2];

		$m = $this->getModel();
		$this->isecure = $m->itemIsSecure($cat[0]);
		if ($this->isecure) {
			$cookn = UserNotesHelper::hashCookieName($cat[0], $cat[1]);
			$cookv = $app->input->cookie->getBase64($cookn);
			$this->key = UserNotesHelper::doCrypt($cat[0].'-@:'.$cat[1], $cookv, true);
		}

		// Get path to file
		$udp = UserNotesHelper::userDataPath();
		$this->fpath = JPATH_BASE.'/'.$udp.'/attach/'.$cat[1].'/'.$cat[2];

		$this->attProps = $m->atFileProps($cat[1]);

		return parent::display($tpl);
	}


}
